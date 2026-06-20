# 次セッション引き継ぎ指示書（OpenStockManager React 移行 / 3-9 ユーザー管理完了後）

> このドキュメントは「次の AI セッションが最短で作業に入れること」を目的としています。
> **まず §1 で現状把握 → §2 で読むべきファイルを開く → §3 の手順で実装** の順に進めてください。

---

## §1. 現状（2026-06-19 時点）

### 完了済みフェーズ
| Phase | 内容 | 状態 |
|-------|------|------|
| 3-2 | ダッシュボード | ✅ |
| 3-3 | 在庫（数量/個別/詳細/バーコード/検索） | ✅ |
| 3-4 | 端末登録（単体+CSV一括） | ✅（画像のみ残） |
| 3-5 | データ（クライアント/担当者/ファイル 全8画面） | ✅ |
| 3-6 | レンタル手続き | ✅（カート端末検索の配線も完了） |
| 3-7 | 販売手続き | ✅ |
| 3-8 | 履歴（レンタル/販売 統合ビュー） | ✅ |
| **3-9** | **設定 - ユーザー管理（admin）** | ✅ **今セッションで完了**（カテゴリ/フィールド/メールは残） |
| 3-9残 | カテゴリ / カスタムフィールド / メール・CRM連携 | ☐ **← 次の最有力タスク** |

### テスト状況
```
API:      319 passed / 1 risky / 3 pre-existing failures（Blade Vite manifest 由来。React 移行とは無関係）
Frontend: build / typecheck  すべて green
```

### 直近セッションで実装した内容
**3-9 設定 - ユーザー管理（admin 限定）**
- **API**: `Api\UserController`（index/store/update）。`auth:sanctum` + `admin` ミドルウェアで保護（非 admin は 403）。
  `StoreUserApiRequest`/`UpdateUserApiRequest`（メール一意・パスワード強度・role in[admin,user]）。`UserApiTest`（12 tests）
  - ルート: `GET /api/users`・`POST /api/users`・`PUT /api/users/{id}`（`Route::middleware('admin')->group`）
  - ⚠️ `users.id` は bigint オートインクリメント。旧 `StoreUserRequest` の UUID 採番は現スキーマと不一致なので不採用。
- **フロント**: `features/users/useUsers.ts`（list/create/update）、`UsersPage`（一覧＋検索＋ページング＋編集モーダル）、
  `UserRegisterPage`（登録）、`auth/AdminRoute.tsx`（非 admin は `/dashboard` へ）。router の `/users`・`/users/register` を `AdminRoute` 配下に。
  Sidebar「設定 > ユーザー」は既存（adminOnly）。
- **register.css**: email/password/tel の input を共通スタイル対象に追加、`__hint` クラスと actions の flex/gap を追加。

**3-8 履歴（統合ビュー）**
- **API**: `Api\HistoryController@index`（`GET /api/history?type=all|rental|sale&word=&page=`）。
  RentalHist / SaleHist を共通フォーマット（id/type/company/contact/date/status/note）に正規化・マージし、
  日付降順で 10 件ページング（`Collection::forPage` による手動ページネーション）。`HistoryApiTest`（6 tests）
- **フロント**: `features/history/useHistory.ts`、`pages/HistoryPage.tsx`（種別タブ すべて/レンタル/販売 ＋
  キーワード検索 ＋ ページング。各行は既存 per-type 詳細へリンク）、router に `/history`、Sidebar「履歴 > 全体」追加
- **方針メモ**: 旧 `history/checkout.blade.php` は未完成スキャフォールドのため移植対象外と判断。

**3-7 販売（前段）**
- `Api\SaleController`（返却なし）、Sale 系 FormRequest 3 種、`/sale*` ルート、`Device::$fillable` に `sale_id`、`SaleApiTest`（12 tests）
- フロント: `features/sale/useSale.ts`、`SalePage`/`SaleCartForm`/`SaleFileForm`/`SaleHistoryPage`/`SaleHistoryDetailPage`、`sale.css`
- カート端末検索の未配線バグを `useDeviceSearch`（300ms デバウンス）で修正（rental/sale 両方）

---

## §2. 次セッションで読むべきファイル（3-9 残：カテゴリ/フィールド/メール を実装する場合）

### 2-1. 旧 Laravel 実装（ロジック・仕様の正解）
```
api/routes/web.php                                   ← admin ルート（grep -n "categories\|fields\|mail" で確認。54-63行 付近）
api/app/Http/Controllers/（DeviceCategory/DeviceTypeField 系コントローラ）
api/app/Models/DeviceCategory.php / DeviceTypeField.php  ← 既存モデル
api/resources/views/device_categories/*, device_fields/*, mailform.blade.php
```

### 2-2. 今あるお手本・関連実装
| 既存ファイル | 内容 |
|-------------|------|
| `api/app/Http/Controllers/Api/UserController.php` + `UserApiTest` | **admin 限定 API のお手本**（`Route::middleware('admin')->group` / 403 / 12 tests） |
| `frontend/src/auth/AdminRoute.tsx` | 非 admin を `/dashboard` へ送るルートガード（カテゴリ/フィールド画面でも流用） |
| `frontend/src/pages/UsersPage.tsx` + `features/users/useUsers.ts` | 一覧＋検索＋ページング＋編集モーダル＋登録ページの典型形 |
| `frontend/src/layouts/Sidebar.tsx` | 「設定」に `/settings/categories`・`/settings/fields`・`/settings/mail`（adminOnly）リンクが既にある＝リンク先を実装するだけ |
| `Device` モデルの `custom_fields`(array cast) / `DeviceTypeField` | カスタムフィールド定義のデータ構造 |

→ **3-9 残の本体は「機材カテゴリ CRUD」「カスタムフィールド CRUD（＋並び替え reorder）」「メール送信・CRM 同期」**。
   admin 保護は UserController と同じ `Route::middleware('admin')->group` でよい。並び替えは drag&drop or 上下ボタン＋ `reorder` API。
   メール連携は外部依存があるため後回し可。まずはカテゴリ CRUD が着手しやすい。

### 2-3. 変更が必要になりうる既存ファイル
```
api/routes/api.php          ← /device-categories, /device-fields 系を admin グループに追加
frontend/src/router.tsx     ← /settings/categories, /settings/fields, /settings/mail を AdminRoute 配下に追加
frontend/src/layouts/Sidebar.tsx ← 設定セクション（リンクは既にあり）
docs/react-laravel-migration.md  ← 3-9 を更新
```

> ⚠️ 既存の 3 failures は `tests/Feature/Middleware/AdminMiddlewareTest.php` 等の **Blade ルート（`/users` GET など）** が
> Vite manifest を要求して落ちているもので、API 移行とは別物。Blade ルート自体を撤去する Phase 4 まで残る見込み。
> （3-9 のユーザー管理は API/React 側で実装済みだが、旧 Blade `/users` ルートは web.php に残してあるため失敗も残存。）

---

## §3. 共通の設計パターン（踏襲すること）

### API（Laravel）
- リスト: `GET /api/xxx` → `{ data: [...], meta: { current_page, last_page, per_page, total } }`
- 詳細: `GET /api/xxx/{id}` → `{ data: {...} }`、未存在は 404 + `{ message }`
- 作成: `POST /api/xxx/store` + 専用 FormRequest → 201 + `{ data, message }`
- CSV: `POST /api/xxx/multi/upload`（解析プレビュー）→ `POST /api/xxx/multi/store`（一括保存）
- バリデーション失敗: 422 + `{ message, errors: { field: [msg] } }`
- 検索: `Keyword` trait の `extractKeywords()`（全角→半角 + スペース区切り AND 検索）
- ルート順: 具体パス（`/search`・`/history`）を `{id}` より**前**に定義
- ⚠️ `devices.lending_now` / `sale_id` は **NOT NULL（デフォルト空文字）**。
  ただし `update()` するモデルは `$fillable` にカラムを含めること（3-7 で `Device::$fillable` に `sale_id` を追加した）。

### フロント（React）
- データ取得: `useQuery`、作成/更新: `useMutation`（`mutateAsync` + try/catch で 422 を捕捉）
- 422 表示: `err.response.data.errors` をフォーム各フィールドに対応付け
- CSV フロー: 3-state component（`upload` → `confirm` → `completed`）
- API クライアント: `import api from '@/lib/api'`（baseURL に `/api` 込み。パスは `/sale` 等）
- 担当者の絞り込み: `useContacts('')` で全件取得 → `client_id` でクライアント側フィルタ
- 端末検索（カート）: `useDeviceSearch(debouncedTerm, '', 1)` を 300ms デバウンスで配線（3-6/3-7 で確立）
- スタイル: 既存 CSS（`rental.css`/`sale.css`）を流用。`.badge`, `.search-pagination` 等は再利用可

---

## §4. API エンドポイント全覧（Phase 3 現在）

| メソッド | パス | 機能 | 状態 |
|---------|------|------|------|
| GET | `/api/dashboard` | ダッシュボード | ✅ |
| GET | `/api/inventory/stocks` | 数量管理 | ✅ |
| GET | `/api/devices/category/{code}` | 個別管理 | ✅ |
| GET | `/api/devices/search` | 端末検索 | ✅ |
| GET | `/api/devices/{id}` | 端末詳細 | ✅ |
| POST | `/api/devices` | 端末登録 | ✅ |
| POST | `/api/devices/multi/{upload,store}` | CSV端末登録 | ✅ |
| GET/POST | `/api/devices/file/{spec,benchmark}` | ファイル管理 | ✅ |
| GET/POST | `/api/clients`, `/api/clients/{id}` | クライアント | ✅ |
| GET/POST | `/api/contacts`, `/api/contacts/{id}` | 担当者 | ✅ |
| GET | `/api/rental` | レンタル一覧 | ✅ |
| POST | `/api/rental/store` | レンタル登録 | ✅ |
| POST | `/api/rental/multi/{upload,store}` | CSV一括レンタル | ✅ |
| POST | `/api/rental/multi/return/{lendId}` | 返却 | ✅ |
| GET | `/api/rental/history{,/{lendId}}` | レンタル履歴 | ✅ |
| GET | `/api/sale` | 販売一覧 | ✅ |
| POST | `/api/sale/store` | 販売登録 | ✅ |
| POST | `/api/sale/multi/{upload,store}` | CSV一括販売 | ✅ |
| GET | `/api/sale/history{,/{saleId}}` | 販売履歴 | ✅ |
| GET | `/api/history?type=&word=&page=` | 統合履歴（レンタル+販売） | ✅ |
| GET/POST | `/api/users`, `PUT /api/users/{id}` | ユーザー管理（admin 限定） | ✅ |

---

## §5. 開始時チェックリスト

```bash
git branch                       # → claude/funny-galileo-6fgy3o
git log --oneline -5             # → 最新が "feat: 3-9 ユーザー管理..." 系
cd api && composer install       # 依存が無ければ
cd api && php artisan test 2>&1 | grep "Tests:"    # → 319 passed / 1 risky / 3 failures
cd ../frontend && npm ci          # node_modules が無ければ（lockfile は TS 5.9.3 を固定）
cd ../frontend && npm run build && npm run typecheck   # → green
```

> ⚠️ 環境メモ: フレッシュなコンテナでは `api/vendor` と `frontend/node_modules` が未インストールのことがある。
> その場合は `composer install`（api）/ `npm ci`（frontend）を先に実行する。
> `npx tsc` を直接叩くと TS 6.0 系が降ってきて `baseUrl` 廃止エラーが出るが、`npm run` 経由なら lockfile の 5.9.3 が使われ問題なし。

---

## §6. 代替タスク（カテゴリ/フィールド以外で進めたい場合）

- **プロフィール画面**（難度🟢低）: `/profile`（自分の情報表示・メール変更）。メール変更はトークン＋通知が絡むので表示のみ先行も可。
- **3-4 残（端末画像）**（難度🟢低）: 端末登録の画像アップロードのみ未実装。
- **販売バリデーション強化**（難度🟢低）: 旧 `StoreSaleCartRequest` は「販売済み/貸出中/不良/販売不可」を弾く。
  現状の API は端末状態チェック未実装（レンタルと同形にしてある）。必要なら `StoreSaleApiRequest::withValidator` で追加。
- **3-10 共通モーダル群 / 3-11 エラーページ**（難度🟢〜🟠）。

---

**ブランチ**: `claude/funny-galileo-6fgy3o`
**次セッション目標**: 3-9 残（機材カテゴリ CRUD → カスタムフィールド CRUD → メール/CRM）。
admin 保護・一覧/編集 UI は `UserController`/`UsersPage`/`AdminRoute` がそのままお手本になる。
