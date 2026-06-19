# 次セッション引き継ぎ指示書（OpenStockManager React 移行 / 3-7 販売完了後）

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
| **3-7** | **販売手続き** | ✅ **今セッションで完了** |
| 3-8 | 履歴（レンタル/販売の統合・絞り込み強化） | ☐ **← 次の最有力タスク** |
| 3-9 | 設定（admin: ユーザー/カテゴリ/カスタムフィールド） | ☐ |

### テスト状況
```
API:      301 passed / 1 risky / 3 pre-existing failures（Blade Vite manifest 由来。React 移行とは無関係）
Frontend: build / typecheck  すべて green
```

### このセッションで実装した内容（3-7 販売 + カート検索修正）
- **API**: `Api\SaleController`（index/store/uploadMulti/storeMulti/history/historyDetail。返却なし）、
  `StoreSaleApiRequest`/`UploadSaleMultiApiRequest`/`StoreSaleMultiApiRequest`、`/sale*` ルート、
  `Device` モデルの `$fillable` に `sale_id` 追加、`SaleApiTest`（12 tests）
- **フロント**: `features/sale/useSale.ts`、`SalePage`/`SaleCartForm`/`SaleFileForm`/
  `SaleHistoryPage`/`SaleHistoryDetailPage`（返却ボタンなし・表示のみ）、`sale.css`、router に 3 ルート
- **既知バグ修正**: `RentalCartForm`/`SaleCartForm` の端末検索が未配線だった問題を解消。
  `useDeviceSearch` をデバウンス（300ms）付きで配線し、`searchTerm` 入力で候補が出るように。
  販売側は販売済み(`sale_id`)・貸出中(`lending_now`)・選択済みの端末を候補から除外。

---

## §2. 次セッションで読むべきファイル（3-8 履歴を実装/強化する場合）

### 2-1. 旧 Laravel 実装（ロジック・仕様の正解）
```
api/app/Http/Controllers/RentalHistsController.php   ← レンタル履歴
api/app/Http/Controllers/SalesHistsController.php    ← 販売履歴
api/resources/views/history/all_rental_historys.blade.php
api/resources/views/history/all_sales_historys.blade.php
api/resources/views/history/checkout.blade.php       ← 貸出明細（未移植）
```

### 2-2. 今あるお手本（履歴ページは既に per-type で実装済）
| 既存ファイル | 内容 |
|-------------|------|
| `frontend/src/pages/RentalHistoryPage.tsx` | レンタル履歴一覧（検索/ページング） |
| `frontend/src/pages/RentalHistoryDetailPage.tsx` | レンタル履歴詳細（返却ボタンあり） |
| `frontend/src/pages/SaleHistoryPage.tsx` | 販売履歴一覧 |
| `frontend/src/pages/SaleHistoryDetailPage.tsx` | 販売履歴詳細（表示のみ） |
| API: `GET /api/rental/history{,/{lendId}}` / `GET /api/sale/history{,/{saleId}}` | いずれも実装済 |

→ **3-8 の本体は「レンタル/販売を 1 画面に統合し、種別フィルタ・期間/ステータス絞り込みを足す」こと。**
   per-type の一覧/詳細は既にあるので、統合ビューを新設するか、既存ページに種別タブを足す方針を決めて着手。
   軽く進めたい場合は、未移植の「貸出明細（checkout）」画面の移植だけでも 1 タスクになる。

### 2-3. 変更が必要になりうる既存ファイル
```
api/routes/api.php          ← 統合履歴 API を足すなら（例: GET /api/history?type=rental|sale）
frontend/src/router.tsx     ← 統合履歴ルートを足すなら
frontend/src/layouts/Sidebar.tsx ← 「履歴」セクション（既にレンタル/販売リンクあり）
docs/react-laravel-migration.md  ← 3-8 を更新
```

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

---

## §5. 開始時チェックリスト

```bash
git branch                       # → claude/funny-galileo-6fgy3o
git log --oneline -5             # → 最新が "feat: 3-7 販売手続き..." 系
cd api && composer install       # 依存が無ければ
cd api && php artisan test 2>&1 | grep "Tests:"    # → 301 passed / 1 risky / 3 failures
cd ../frontend && npm ci          # node_modules が無ければ（lockfile は TS 5.9.3 を固定）
cd ../frontend && npm run build && npm run typecheck   # → green
```

> ⚠️ 環境メモ: フレッシュなコンテナでは `api/vendor` と `frontend/node_modules` が未インストールのことがある。
> その場合は `composer install`（api）/ `npm ci`（frontend）を先に実行する。
> `npx tsc` を直接叩くと TS 6.0 系が降ってきて `baseUrl` 廃止エラーが出るが、`npm run` 経由なら lockfile の 5.9.3 が使われ問題なし。

---

## §6. 代替タスク（履歴以外で進めたい場合）

- **3-9 設定/admin**（難度🟠中、4-5h）: ユーザー管理・カテゴリ・カスタムフィールド。`admin` ミドルウェア検証含む。
- **3-4 残（端末画像）**（難度🟢低）: 端末登録の画像アップロードのみ未実装。
- **販売バリデーション強化**（難度🟢低）: 旧 `StoreSaleCartRequest` は「販売済み/貸出中/不良/販売不可」を弾く。
  現状の API は端末状態チェック未実装（レンタルと同形にしてある）。必要なら `StoreSaleApiRequest::withValidator` で追加。

---

**ブランチ**: `claude/funny-galileo-6fgy3o`
**次セッション目標**: 3-8 履歴（統合ビュー or 貸出明細移植）。per-type の履歴一覧/詳細は実装済みなので統合方針を決めて着手。
