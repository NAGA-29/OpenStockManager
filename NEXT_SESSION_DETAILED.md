# 次セッション引き継ぎ指示書（OpenStockManager React 移行 / 3-8 履歴完了後）

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
| **3-8** | **履歴（レンタル/販売 統合ビュー）** | ✅ **今セッションで完了** |
| 3-9 | 設定（admin: ユーザー/カテゴリ/カスタムフィールド） | ☐ **← 次の最有力タスク** |

### テスト状況
```
API:      307 passed / 1 risky / 3 pre-existing failures（Blade Vite manifest 由来。React 移行とは無関係）
Frontend: build / typecheck  すべて green
```

### 直近セッションで実装した内容
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

## §2. 次セッションで読むべきファイル（3-9 設定/admin を実装する場合）

### 2-1. 旧 Laravel 実装（ロジック・仕様の正解）
```
api/app/Http/Controllers/UsersController.php          ← ユーザー管理（一覧/登録/プロフィール）
api/app/Http/Controllers/DeviceCategoryController.php ← 機材カテゴリ（存在すれば）
api/resources/views/user/*.blade.php                  ← ユーザー画面
api/routes/web.php                                    ← admin ミドルウェアのルート（grep -n "admin\|user" で確認）
```

### 2-2. 今あるお手本・関連実装
| 既存ファイル | 内容 |
|-------------|------|
| `api/app/Http/Middleware/*Admin*` + `tests/Feature/Middleware/AdminMiddlewareTest.php` | admin 判定（既存・ただし 3 failures はこの周辺の Blade ルート由来） |
| `frontend/src/auth/useAuth.ts` | `user.is_admin` を保持（Sidebar の adminOnly 出し分けで使用済み） |
| `frontend/src/layouts/Sidebar.tsx` | 「設定」セクションに `/users`・`/settings/categories`・`/settings/fields`（adminOnly）リンクが既にある＝リンク先を実装するだけ |
| 各種 CRUD のお手本 | `Api\ClientController` / `Api\ContactController`（一覧+登録+詳細の典型形） |

→ **3-9 の本体はユーザー管理 CRUD（admin 限定）**。`auth:sanctum` に加え admin 認可をどう表現するか
   （Policy / Gate / ミドルウェア）を最初に決める。カテゴリ・カスタムフィールド管理も同セクション。

### 2-3. 変更が必要になりうる既存ファイル
```
api/routes/api.php          ← /users 系などを admin 認可付きで追加
frontend/src/router.tsx     ← /users, /settings/* ルート追加
frontend/src/layouts/Sidebar.tsx ← 設定セクション（リンクは既にあり）
docs/react-laravel-migration.md  ← 3-9 を更新
```

> ⚠️ 既存の 3 failures は `tests/Feature/Middleware/AdminMiddlewareTest.php` 等の **Blade ルート（`/users` GET）** が
> Vite manifest を要求して落ちているもの。3-9 で `/users` を React 化し API へ寄せる過程で解消できる可能性がある。

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

---

## §5. 開始時チェックリスト

```bash
git branch                       # → claude/funny-galileo-6fgy3o
git log --oneline -5             # → 最新が "feat: 3-8 履歴..." 系
cd api && composer install       # 依存が無ければ
cd api && php artisan test 2>&1 | grep "Tests:"    # → 307 passed / 1 risky / 3 failures
cd ../frontend && npm ci          # node_modules が無ければ（lockfile は TS 5.9.3 を固定）
cd ../frontend && npm run build && npm run typecheck   # → green
```

> ⚠️ 環境メモ: フレッシュなコンテナでは `api/vendor` と `frontend/node_modules` が未インストールのことがある。
> その場合は `composer install`（api）/ `npm ci`（frontend）を先に実行する。
> `npx tsc` を直接叩くと TS 6.0 系が降ってきて `baseUrl` 廃止エラーが出るが、`npm run` 経由なら lockfile の 5.9.3 が使われ問題なし。

---

## §6. 代替タスク（設定以外で進めたい場合）

- **3-4 残（端末画像）**（難度🟢低）: 端末登録の画像アップロードのみ未実装。
- **販売バリデーション強化**（難度🟢低）: 旧 `StoreSaleCartRequest` は「販売済み/貸出中/不良/販売不可」を弾く。
  現状の API は端末状態チェック未実装（レンタルと同形にしてある）。必要なら `StoreSaleApiRequest::withValidator` で追加。
- **3-10 共通モーダル群 / 3-11 エラーページ**（難度🟢〜🟠）。

---

**ブランチ**: `claude/funny-galileo-6fgy3o`
**次セッション目標**: 3-9 設定/admin（ユーザー管理 CRUD を admin 認可付きで API 化＋React 化）。
既存の 3 failures（Blade `/users` ルート）を React 移行で解消できる可能性あり。
