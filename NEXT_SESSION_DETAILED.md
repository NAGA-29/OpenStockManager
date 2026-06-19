# 次セッション引き継ぎ指示書（OpenStockManager React 移行 / 3-6 レンタル完了後）

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
| **3-6** | **レンタル手続き** | ✅ **今セッションで完了** |
| 3-7 | 販売 | ☐ **← 次の最有力タスク** |
| 3-8 | 履歴統合 | ☐ |
| 3-9 | 設定（admin） | ☐ |

### テスト状況
```
API:      289 passed / 1 risky / 3 pre-existing failures（Blade Vite manifest 由来。React 移行とは無関係）
Frontend: build / typecheck / lint  すべて green
```

### ⚠️ 既知の課題（3-6 レンタルの未完部分）
- **`RentalCartForm.tsx` の端末検索が未配線**。`searchTerm` / `searchResults` の state はあるが、`useDeviceSearch` フックに繋いでいないため、検索ボックスに入力しても候補が出ない。
  - → 次セッションで `frontend/src/features/inventory/useDeviceSearch.ts` を import し、`searchTerm` を渡して `searchResults` を埋める配線が必要。
  - 販売（3-7）でも同じカート UI を使うので、**先にこの検索配線を共通部品化してから 3-7 に進む**のが効率的。

---

## §2. 次セッションで読むべきファイル（3-7 販売を実装する場合）

### 2-1. 旧 Laravel 実装（ロジック・仕様の正解はここ）
```
api/app/Http/Controllers/SalesHistsController.php   ← 販売の業務ロジック（必読）
api/app/Models/SaleHist.php                          ← モデル定義（既読: sale_id/client/contact/staff/sale_date_at/note）
api/app/Http/Requests/StoreSaleCartRequest.php       ← カート販売のバリデーション
api/app/Http/Requests/UploadSaleFileRequest.php      ← CSV 販売のバリデーション
api/resources/views/sales/sales.blade.php            ← 販売手続き UI
api/resources/views/sales/multi_sale_confirm.blade.php ← CSV 確認 UI
api/resources/views/sales/sales_detail.blade.php     ← 販売詳細 UI
```
旧ルート定義: `api/routes/web.php` の **75-77行 / 135-144行**（`grep -n "sale" api/routes/web.php`）

### 2-2. 今セッションで作った「お手本」（これをコピー&置換するのが最速）
| 新規作成するファイル | コピー元（3-6 レンタル） |
|---------------------|------------------------|
| `api/app/Http/Controllers/Api/SaleController.php` | `api/app/Http/Controllers/Api/RentalController.php` |
| `api/app/Http/Requests/StoreSaleApiRequest.php` | `api/app/Http/Requests/StoreRentalApiRequest.php` |
| `api/app/Http/Requests/UploadSaleMultiApiRequest.php` | `api/app/Http/Requests/UploadRentalMultiApiRequest.php` |
| `api/app/Http/Requests/StoreSaleMultiApiRequest.php` | `api/app/Http/Requests/StoreRentalMultiApiRequest.php` |
| `api/tests/Feature/Api/SaleApiTest.php` | `api/tests/Feature/Api/RentalApiTest.php` |
| `frontend/src/features/sale/useSale.ts` | `frontend/src/features/rental/useRental.ts` |
| `frontend/src/pages/SalePage.tsx` | `frontend/src/pages/RentalPage.tsx` |
| `frontend/src/components/sale/SaleCartForm.tsx` | `frontend/src/components/rental/RentalCartForm.tsx` |
| `frontend/src/components/sale/SaleFileForm.tsx` | `frontend/src/components/rental/RentalFileForm.tsx` |
| `frontend/src/pages/SaleHistoryPage.tsx` | `frontend/src/pages/RentalHistoryPage.tsx` |
| `frontend/src/pages/SaleHistoryDetailPage.tsx` | `frontend/src/pages/RentalHistoryDetailPage.tsx` |
| `frontend/src/pages/sale.css` | `frontend/src/pages/rental.css` |

### 2-3. 変更が必要な既存ファイル
```
api/routes/api.php                  ← 販売ルートを auth:sanctum グループに追加
frontend/src/router.tsx             ← /sale, /sale/history, /sale/history/:saleId を追加
frontend/src/layouts/Sidebar.tsx    ← 「手続き > 販売」「履歴 > 販売」は既にリンクあり（リンク先を実装するだけ）
docs/react-laravel-migration.md     ← 3-7 を ☑ に更新
```

---

## §3. レンタル → 販売の「差分」だけ押さえる（重要）

レンタルと販売は **9割同じ**。違うのは以下だけ：

| 観点 | レンタル (3-6) | 販売 (3-7) |
|------|---------------|-----------|
| 主キー | `lend_id` | `sale_id` |
| 中間テーブル | `device_rental`（pivot: checkout_at, return_at） | `device_sale`（pivot: sale_date_at のみ） |
| 端末フラグ | `devices.lending_now` に lend_id をセット/空に戻す | `devices.sale_id` に sale_id をセット（**戻す概念なし＝返却なし**） |
| 日付項目 | checkout_at + schedule_return_at + return_at | **sale_date_at のみ** |
| 返却処理 | **あり**（`POST /rental/multi/return/{lendId}`） | **なし**（販売は不可逆） |
| 詳細画面 | 返却ボタンあり | 返却ボタン不要（表示のみ） |

→ つまり SaleController は RentalController から **返却関連メソッド (`returnDevice`) を削除**し、フィールド名を置換するだけ。
→ `SaleHistoryDetailPage` も返却ボタンを消すだけ。

### 実装手順
1. §2-1 の旧実装を読み、販売特有のバリデーション（例: 既に販売済みの端末を弾く等）を確認
2. §2-2 の対応表どおりにファイルをコピーして sale 用に置換
3. ルート追加（`api/routes/api.php`、`frontend/src/router.tsx`）
4. 検証:
   ```bash
   cd api && php artisan test tests/Feature/Api/SaleApiTest.php
   cd ../frontend && npm run build
   ```

---

## §4. 共通の設計パターン（3-6 で確立済み・踏襲すること）

### API（Laravel）
- リスト: `GET /api/xxx` → `{ data: [...], meta: { current_page, last_page, per_page, total } }`
- 詳細: `GET /api/xxx/{id}` → `{ data: {...} }`、未存在は 404 + `{ message }`
- 作成: `POST /api/xxx/store` + 専用 FormRequest → 201 + `{ data, message }`
- CSV: `POST /api/xxx/multi/upload`（解析プレビュー）→ `POST /api/xxx/multi/store`（一括保存）
- バリデーション失敗: 422 + `{ message, errors: { field: [msg] } }`
- 検索: `Keyword` trait の `extractKeywords()`（全角→半角 + スペース区切り AND 検索）
- ルート順: 具体パス（`/search`）を `{id}` より**前**に定義
- ⚠️ `devices.lending_now` / `sale_id` は **NOT NULL（デフォルト空文字）**。`null` でなく `''` を入れること（3-6 でハマった箇所）

### フロント（React）
- データ取得: `useQuery`、作成/更新: `useMutation`（`mutateAsync` + try/catch で 422 を捕捉）
- 422 表示: `err.response.data.errors` をフォーム各フィールドに対応付け
- CSV フロー: 3-state component（`upload` → `confirm` → `completed`）
- API クライアント: `import api from '@/lib/api'`（baseURL に `/api` 込み。パスは `/rental` 等）
- 担当者の絞り込み: `useContacts('')` で全件取得 → `client_id` でクライアント側フィルタ（3-6 で採用）
- スタイル: 既存 CSS（`rental.css`）を流用。`.badge`, `.search-pagination` 等は再利用可

---

## §5. API エンドポイント全覧（Phase 3 現在）

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
| — | `/api/sale*` | 販売 | ☐ **未実装** |

---

## §6. 開始時チェックリスト

```bash
git branch                       # → claude/upbeat-maxwell-ft3dhm
git log --oneline -5             # → 最新が "docs: 3-6 ... 反映" / "feat: 3-6 レンタル..."
cd api && php artisan test 2>&1 | grep "Tests:"    # → 289 passed / 1 risky
cd ../frontend && npm run build 2>&1 | tail -3     # → built in X.XXs
```

---

## §7. 代替タスク（販売以外で軽く進めたい場合）

- **3-8 履歴統合**（難度🟢低、2-3h）: レンタル/販売履歴を1画面に統合。読み取り中心で着手しやすい。
- **3-9 設定/admin**（難度🟠中、4-5h）: ユーザー管理・カテゴリ・カスタムフィールド。`admin` ミドルウェア検証（実質 1-6 達成回）。

---

**ブランチ**: `claude/upbeat-maxwell-ft3dhm`
**最新コミット**: `docs: 3-6 レンタル完了を引き継ぎ指示書に反映・3-7販売へのテンプレート追加`
**次セッション目標**: 3-7 販売（§2-2 の対応表どおりコピー&置換が最速）。先に §1 の「カート検索未配線」を共通化推奨。
