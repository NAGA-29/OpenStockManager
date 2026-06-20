# 次セッション引き継ぎ指示書（OpenStockManager React 移行 / 3-9 機材カテゴリ完了後）

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
| **3-9** | **設定 - ユーザー管理 + 機材カテゴリ（admin）** | ✅ **今セッションで完了**（カスタムフィールド/メールは残） |
| 3-9残 | カスタムフィールド（reorder付き） / メール・CRM連携 | ☐ **← 次の最有力タスク** |

### テスト状況
```
API:      331 passed / 1 risky / 3 pre-existing failures（Blade Vite manifest 由来。React 移行とは無関係）
Frontend: build / typecheck  すべて green
```

### 直近セッションで実装した内容
**3-9 設定 - 機材カテゴリ（admin 限定）**
- **API**: `Api\DeviceCategoryController`（index/store/update/destroy/reorder）。`DeviceCategoryApiTest`（12 tests）
  - `GET/POST /api/device-categories`、`PUT/DELETE /api/device-categories/{id}`、`POST /api/device-categories/reorder`
  - コード変更時は `devices.device_type` を追従更新（トランザクション）。機材が紐づくカテゴリは削除拒否（422）。
  - ⚠️ `nullable` フィールド（icon）は `safe()->all()` に含まれないため `$safe['icon'] ?? null` で受ける（ハマりどころ）。
  - `StoreDeviceCategoryApiRequest`/`UpdateDeviceCategoryApiRequest`（code は `regex:/^[A-Z0-9_]+$/`・一意）
- **フロント**: `features/settings/useDeviceCategories.ts`、`DeviceCategoriesPage`（一覧＋追加フォーム＋編集モーダル＋削除＋上下ボタンで並び替え）、router の `/settings/categories` を `AdminRoute` 配下に。

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

## §2. 次セッションで読むべきファイル（3-9 残：カスタムフィールド／メール を実装する場合）

### 2-1. 旧 Laravel 実装・既存資産（ロジック・仕様の正解）
```
api/app/Http/Controllers/DeviceTypeFieldController.php   ← カスタムフィールドの CRUD/reorder（旧 Blade）
api/app/Models/DeviceTypeField.php                       ← フィールド定義モデル（device_category_code で紐づく）
api/database/migrations/2026_02_19_000001_create_device_type_fields_table.php  ← スキーマ
api/resources/views/device_fields/*, mailform.blade.php
api/routes/web.php                                       ← admin ルート（grep -n "fields\|mail"）
```

### 2-2. 今あるお手本・関連実装（カテゴリ CRUD がほぼそのまま雛形）
| 既存ファイル | 内容 |
|-------------|------|
| `api/app/Http/Controllers/Api/DeviceCategoryController.php` + `DeviceCategoryApiTest` | **CRUD＋reorder の最適なお手本**（admin グループ / 422 / 404 / reorder / 12 tests） |
| `api/app/Http/Requests/{Store,Update}DeviceCategoryApiRequest.php` | FormRequest 雛形（一意・regex・ignore） |
| `frontend/src/pages/DeviceCategoriesPage.tsx` + `features/settings/useDeviceCategories.ts` | 一覧＋追加フォーム＋編集モーダル＋削除＋上下並び替えの完成形 |
| `frontend/src/auth/AdminRoute.tsx` | 非 admin を `/dashboard` へ送るルートガード |
| `frontend/src/layouts/Sidebar.tsx` | 「設定」に `/settings/fields`・`/settings/mail`（adminOnly）リンクが既にある＝リンク先を実装するだけ |
| `Device` モデルの `custom_fields`(array cast) | 端末側の値の持ち方 |

→ **3-9 残の本体は「カスタムフィールド CRUD（＋reorder）」と「メール送信・CRM 同期」**。
   フィールド CRUD は **DeviceCategory 実装をコピーして DeviceTypeField 用に置換するのが最速**（カテゴリコード単位の絞り込みが増える点に注意）。
   メール連携は外部依存があるため後回し可。

### 2-3. 変更が必要になりうる既存ファイル
```
api/routes/api.php          ← /device-fields 系を admin グループに追加（reorder は {id} より前）
frontend/src/router.tsx     ← /settings/fields, /settings/mail を AdminRoute 配下に追加
frontend/src/layouts/Sidebar.tsx ← 設定セクション（リンクは既にあり）
docs/react-laravel-migration.md  ← 3-9 を更新
```

> ⚠️ ハマりどころ: API FormRequest の `nullable` フィールドは未送信だと `safe()->all()` に**含まれない**。
> `$safe['key'] ?? null` で受けること（カテゴリの icon で 500 になった）。

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
| GET/POST/PUT/DELETE | `/api/device-categories{,/{id}}` + `/reorder` | 機材カテゴリ管理（admin 限定） | ✅ |

---

## §5. 開始時チェックリスト

```bash
git branch                       # → claude/funny-galileo-6fgy3o
git log --oneline -5             # → 最新が "feat: 3-9 機材カテゴリ..." 系
cd api && composer install       # 依存が無ければ
cd api && php artisan test 2>&1 | grep "Tests:"    # → 331 passed / 1 risky / 3 failures
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
**次セッション目標**: 3-9 残（カスタムフィールド CRUD＋reorder → メール/CRM）。
`DeviceCategoryController`/`DeviceCategoriesPage`/`useDeviceCategories` をコピー＆置換するのが最速。
