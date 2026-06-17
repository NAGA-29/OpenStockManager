# 引き継ぎ指示書（OpenStockManager React 移行）

> 次セッション（AI）への申し送り。進捗の単一ソースは `docs/react-laravel-migration.md`。
> 本ファイルは「次に何をやるか」を素早く把握するための補助。最新状況は必ず
> `docs/react-laravel-migration.md` §5（末尾が最新）を確認すること。

## 0. 最初に読むもの
- `docs/react-laravel-migration.md` が進捗管理の単一ソース。
  **§2 チェックリスト → §3 画面対応表 → §4 ワークフロー/DoD → §5 申し送り（末尾が最新）** の順で読む。
- 作業は環境システム指示で指定された開発ブランチ上で行い、push のみ（PR は新規作成しない）。

## 1. 現在地（直近セッションで完了）
1タスク=1コミット、すべて push 済み:
- 3-5 担当者(contacts)一覧＋詳細（＋既知 ContactsTest 不整合の解消）
- 3-5 クライアント登録

フェーズ状況:
- **Phase 1（API基盤）**: 1-1〜1-5 完了。1-6 は admin alias 登録済み・AdminMiddleware ガード非依存のため、
  admin 専用 API を足す回（3-9 等）で `->middleware('admin')` 付与により実質達成。
- **Phase 2（フロント基盤）**: 2-1〜2-7 すべて完了。
- **Phase 3（画面移行）**:
  - 3-1 認証: ◐（ログインのみ。残り6画面は対応 API 未実装）
  - 3-2 ダッシュボード: ☑
  - 3-3 在庫: ◐（数量管理／個別管理／端末詳細 完了。**バーコード・検索のみ残**）
  - 3-4 端末登録: ◐（単体登録 完了。**画像アップロード／CSV一括／確認 残**）
  - 3-5 データ: ◐（クライアント一覧/詳細/登録・担当者一覧/詳細 完了。**担当者登録／スペック・ベンチマークファイル 残**）
  - 3-6〜3-11: ☐
- **Phase 4（仕上げ・撤去）**: 4-1〜4-6 すべて ☐（全画面移行後）。

## 2. 実装済み API（フロントが使える窓口）
- 認証: `POST /api/auth/login`・`GET /api/auth/me`・`POST /api/auth/logout`
- `GET /api/dashboard`（フラットキー: `lending_count`/`near_deadline`/`overdue`）
- `GET /api/inventory/stocks`（`{data}` 包み）
- `GET /api/devices/category/{code}`（タブ用 `categories`・`counts`・行）
- `GET /api/devices/form-options`（カテゴリ＋カスタムフィールド定義＋コンディション）※ `/devices/{deviceId}` より**前**にルート定義
- `POST /api/devices`（単体登録、`StoreDeviceApiRequest` で 422 JSON、device_id 自動採番、画像なし）
- `GET /api/devices/{deviceId}`（詳細＋解決済み custom_fields＋貸出/販売履歴）
- `GET /api/clients`（`word` で会社名 like 検索、`{data}` 包み）
- `POST /api/clients`（`StoreClientApiRequest`、client_id 自動採番、422 JSON、成功 201）
- `GET /api/clients/{clientId}`（企業情報＋担当者 contacts）
- `GET /api/contacts`（`word` で担当者名 like 検索、soft-delete 除外、所属企業名 `company` 込み、`{data}` 包み）
- `GET /api/contacts/{contactId}`（担当者詳細＋企業名、未知 ID は 404）

## 3. 次にやるタスク（推奨順）

### ★最優先: 3-5 残「担当者登録」
- 旧画面 `contacts/register`→ React ルート `/contacts/register`、API `POST /api/contacts`。
- 旧 `app/Http/Requests/StoreContactRequest.php` を参照し **`StoreContactApiRequest`** を新設（失敗時 422 JSON）。
  バリデーションに **`client_id` の存在検証**（`exists:clients,client_id`）を入れる。旧 `ContactsController@register` の
  保存ロジック（`client_id`/`name`/`email`/`tel`/`note`）を踏襲。
- `ClientController@store`／`StoreClientApiRequest` の実装と `ClientRegisterPage`／`useRegisterClient` がそのまま雛形になる
  （**所属クライアントは select で選ばせる**＝`GET /api/clients` の結果を使う）。
- `ContactController@store` を追加し `POST /api/contacts` をルート登録、`ContactApiTest` に
  store 成功/422/未知client_id ケースを追加。

### 次点
- **3-5 残: スペック・ベンチマークファイル画面**（`device_spec_file`／`device_benchmark_file`＝
  `GET/POST /api/devices/file/spec|benchmark`）。ファイルアップロードを伴うため難度中。
  サイドバー「データ一覧 > 商品データ」が `/device/file/benchmark` を指している。
- **3-4 残: 端末画像アップロード／CSV一括登録・確認**（難度高。ImageProcessor/Storage・複数ステップ。個別設計回で）。
- **3-3 残: バーコード印刷・検索**（カメラ/バーコードスキャン含む。難度高。後回し推奨）。
- いずれも**着手前に「対応 API が実装済みか」を必ず確認**。

## 4. 厳守ルール
- 1タスク=1コミット。メッセージは `feat(react-migration): <内容> (<タスク番号>)` 形式。
  コミット末尾に `Co-Authored-By` と `Claude-Session` 行を付ける（既存コミット参照）。
- **検証してから push**:
  - frontend: `cd frontend && npm install && npm run typecheck && npm run build && npm run lint`（lint は `--max-warnings 0`）
  - api: `cd api && php artisan test`
- 完了したら `docs/react-laravel-migration.md` の §2/§3 該当行を ☑/◐ 更新＋§5 末尾に申し送り追記。
- 既存の挙動・バリデーション・admin 権限を踏襲（**Blade は壊さない**）。
- **PR は新規作成しない**。指定ブランチへの push のみ。

## 5. 環境メモ / 落とし穴
- **api テスト前提（毎回必要）**:
  1. `cd api && composer install`
  2. `.env` 用意（`cp .env.example .env` → `php artisan key:generate --force`）
  3. `touch database/database.sqlite` → `php artisan migrate --force`
  4. `.env` の `MAIL_FROM_ADDRESS` に実アドレス（例 `test@example.com`）を入れる。
     `null` だと Spatie backup の boot 検証で全テスト落ち。**`.env.example` 自体は変更しない（CI が真実）**。
  5. 一部 Feature テストは Blade 描画のため事前に `cd api && npm install && npm run build`（Vite manifest 生成）が必要。
- ⚠️ `api/package-lock.json` は元々未追跡。手順の `npm install` で生成されるが**コミットに含めない**
  （push 前に `rm -f api/package-lock.json`）。Stop hook が untracked を検知するので**作業ツリーを CLEAN にしてから push**。
- **既知の失敗は現状ゼロ**（前セッションで ContactsTest 不整合を解消済み）。直近の全体実行は **269 passed / 1 risky**。
  risky 1 件は移行前から存在（自分の変更ではない）。それ以外の失敗が出たら自分の変更を疑う。
- `conditions` テーブルはマイグレーションで id 1-4（新品/新古品/中古品/ジャンク品）が seed 済み・timestamps 列なし。
  テストで `Condition::create()` すると `updated_at` 列エラーになるので既存 id を使う。
- `Client`/`Contacts` モデルは `UPDATED_AT = 'modified_at'`（`created_at`/`modified_at` は Eloquent が自動充填）。
  `Client` の主キーは文字列 `client_id`（非incrementing）、`Contacts` の主キーは auto-increment `id`。

## 6. フロント実装パターン（既存資産）
- **データ取得**: TanStack Query（`src/lib/queryClient.ts` 配線済み）。feature 別フックは `src/features/<domain>/use*.ts`。
  参考: `features/clients/`（`useClients`/`useClient`/`useRegisterClient`）、`features/contacts/`（`useContacts`/`useContact`）、`features/inventory/`。
- **登録フォーム（mutation＋422）の雛形**: `pages/ClientRegisterPage.tsx`＋`useRegisterClient.ts`（または `RegisterDevicePage.tsx`）。
  `AxiosError<{message,errors}>` を見てフィールド単位表示＋トースト、成功時は詳細リンクバナー＋一覧クエリ invalidate＋フォームリセット。
- **共通 UI**: `src/components/ui/`（`Loading` / `Alert`（`AlertVariant`）/ `DataTable<T>`（`Column<T>`・render/empty/rowKey）/
  `Modal` / `toast`（`useToast().show(msg, variant)`））。スタイルは `ui.css`、ユーティリティ `.page-bar`/`.text-danger`/`.text-warning`/`.text-success`。
- **レイアウト/ルート**: 保護ページは `router.tsx` の `ProtectedRoute` → `AppLayout` 配下に1行追加。
  サイドバー定義は `layouts/Sidebar.tsx` の `NAV_SECTIONS`。admin 限定項目は `adminOnly: true`（`useAuth().user.is_admin` で出し分け）。
- **lint 注意**: `react-refresh/only-export-components` のため **1ファイル=1コンポーネント**。
  context/hook はコンポーネントと別ファイルへ。型（interface/type）は同居 OK。
- **共有 CSS**: 全ページを `router.tsx` で静的 import しているため CSS は単一バンドルに同梱。
  再利用時はそのクラスを使う（`inventory.css` の `.device-card`/`.device-info-table`/`.osm-btn`、
  `dashboard.css` の `.summary-card*`、`register.css`、`clients.css`）。重複定義しない。

## 7. 既知の残課題（バックログ）
- ナビ上部のカートボタン（旧 `InCartModal`）＝ Phase 3-10。
- ナビのユーザードロップダウンは外側クリックで閉じる挙動が未実装（トグルのみ）。
- 端末詳細の操作ボタン（編集モーダル=3-10／貸出・販売・返却=3-6/3-7／バーコード印刷）未実装。
- 端末一覧の一括選択チェックボックス（カート）＝ 3-10。
- 一覧系（clients/contacts/devices）はページネーション未実装（全件取得・`word` 検索のみ）。旧 Blade は 10 件ページング。
- 3-1 認証の残り6画面（パスワードリセット／メール認証／メール変更／ユーザー登録）は対応 API 未実装のため未着手。
