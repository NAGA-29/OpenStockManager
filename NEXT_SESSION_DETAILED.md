# 次セッション引き継ぎ指示書（OpenStockManager React 移行 3-6 完了後）

## 直近完了内容（過去セッション含む）

### ✅ 3-6 レンタル手続き 完全完了
- **API**: 7エンドポイント実装済
  - `GET /api/rental` → レンタル一覧（ページング・検索対応）
  - `POST /api/rental/store` → レンタル登録
  - `GET /api/rental/history` → レンタル履歴一覧（ページング・検索対応）
  - `GET /api/rental/history/{lendId}` → 履歴詳細
  - `POST /api/rental/multi/upload` → CSV解析・プレビュー
  - `POST /api/rental/multi/store` → 一括登録
  - `POST /api/rental/multi/return/{lendId}` → 個別返却処理
- **フロント**: 4画面実装済
  - `RentalPage`（タブUI：カート式・ファイル式）
  - `RentalHistoryPage`（一覧・検索・ページング）
  - `RentalHistoryDetailPage`（詳細・返却処理）
- **テスト**: 12個の API テスト全て合格

### ✅ 3-5 フェーズ 完全完了（全 8 画面）
- クライアント: 一覧 ✅ / 詳細 ✅ / 登録 ✅
- 担当者: 一覧 ✅ / 詳細 ✅ / 登録 ✅
- ファイル: スペック ✅ / ベンチマーク ✅

### ✅ 3-4 CSV 一括登録 完了
- **API**: `POST /api/devices/multi/{upload,store}`（upload=CSV解析プレビュー / store=一括保存）
- **フロント**: `/device/register/multi`（3-state component）

### ✅ 3-3 完全完了（バーコード・検索）
- **API**: `GET /api/devices/search`（複数キーワード AND 検索・10件ページング）
- **フロント**: `DeviceSearchPage`（検索フォーム・ページネーション）、`DeviceBarcodePage`（jsbarcode CODE128 印刷）

### テスト状況
```
API: 289 passed / 1 risky / 3 pre-existing failures（Blade Vite manifest）
Frontend: build / typecheck / lint ✅ green
```

---

## 推奨される次のタスク（優先順）

### ⭐ オプション 1️⃣: 3-7（手続き・販売）【推奨】
**難度**: 🟠 中 | **所要時間**: 4-5h

3-6 と同じパターンで実装可能（レンタル → 販売への置き換え）：
- API エンドポイント: `GET /api/sale`, `POST /api/sale/store`, `GET /api/sale/history`, `GET /api/sale/history/{saleId}`, `POST /api/sale/multi/{upload,store}`
- フロント: SalePage（カート式・ファイル式）、SaleHistoryPage、SaleHistoryDetailPage

参考：旧 `api/resources/views/sale/` ディレクトリ

### オプション 2️⃣: 3-8（履歴統合）
**難度**: 🟢 低～中 | **所要時間**: 2-3h

レンタル・販売の履歴を統合表示する履歴ダッシュボード：
- API エンドポイント: `GET /api/history`（全取引の統合履歴）
- フロント: HistoryPage（タブ：レンタル/販売、統合検索）

### オプション 3️⃣: 3-9（設定・admin）
**難度**: 🟠 中 | **所要時間**: 4-5h

- ユーザー管理: `GET /api/users`, `POST /api/users`, admin ミドルウェア検証
- カテゴリ管理: `GET /api/categories`, `POST /api/categories`
- カスタムフィールド: `GET /api/custom-fields`, `POST /api/custom-fields`

---

## 3-7 販売を実装する場合の手順

1. **旧実装を読む**:
   ```bash
   # コントローラ（ロジック確認）
   api/app/Http/Controllers/SaleController.php
   # Blade（UI 確認）
   api/resources/views/sale/
   # ルート（エンドポイント確認）
   grep "sale" api/routes/web.php
   ```

2. **API 実装（新規 `Api\SaleController`）**:
   - 3-6 RentalController をテンプレートに、フィールド名を置き換え
   - SaleHist モデル（sale_id, client, contact, staff, sale_date_at, note）
   - Request クラス: StoreSaleApiRequest, UploadSaleMultiApiRequest, StoreSaleMultiApiRequest

3. **フロント実装**:
   - useSale.ts（useRental.ts をコピー・置き換え）
   - SalePage / SaleHistoryPage / SaleHistoryDetailPage
   - sale.css（rental.css を参考）

4. **テスト**:
   ```bash
   php artisan test tests/Feature/Api/SaleApiTest.php
   npm run build && npm run typecheck
   ```

---

## 3-6 の実装パターン（今後の参考）

### API 設計（成功パターン）
- **リスト**: `GET /api/resource` → `{ data: [...], meta: { ... } }`
- **詳細**: `GET /api/resource/{id}` → `{ data: {...} }`
- **作成**: `POST /api/resource/store` + `StoreResourceApiRequest` → 201 + `{ data, message }`
- **CSV一括**: `POST /api/resource/multi/upload` → CSV解析、`POST /api/resource/multi/store` → 一括保存
- **バリデーション失敗**: 422 + `{ message?, errors: { fieldName: [msg1, msg2] } }`

### フロント パターン（成功パターン）
- **ハック検証**: FormRequest の rules を参考に同じバリデーション
- **エラー表示**: 422 応答時に errors オブジェクトをフォーム フィールドに対応付け
- **非同期**: mutateAsync + try/catch で onSuccess/onError を実装
- **状態管理**: useState で form / errors / message / state を管理
- **CSV処理**: 3-state component（upload → confirm → completed）
- **スタイル**: 既存 CSS（rental.css）を参考に、同じパターンを踏襲

### コンポーネント構成（テンプレート）
```
pages/RentalHistoryPage.tsx（一覧）
  ├─ features/rental/useRental.ts（データフェッチ）
  └─ components/ui/DataTable.tsx（表示）

pages/RentalHistoryDetailPage.tsx（詳細）
  └─ useReturnDevice（個別操作）

pages/RentalPage.tsx（手続き）
  ├─ components/rental/RentalCartForm.tsx（カート式）
  ├─ components/rental/RentalFileForm.tsx（ファイル式）
  └─ pages/rental.css（スタイル）
```

---

## 確認チェックリスト（次セッション開始時）

```bash
# ブランチ確認
git branch  # → claude/upbeat-maxwell-ft3dhm であること

# 最新状態確認
git log --oneline -5

# テスト環境確認
cd api && php artisan test 2>&1 | grep "Tests:"
# → 289 passed / 1 risky であること

# フロント ビルド確認
cd ../frontend && npm run build 2>&1 | tail -3
# → built in X.XXs で成功すること

# ラウタ確認
grep -E "rental|sale" frontend/src/router.tsx
# → /rental, /rental/history, /rental/history/:lendId があること
```

---

## API エンドポイント全覧（Phase 3 完了時点）

| メソッド | エンドポイント | 機能 | 状態 |
|---------|---------------|------|------|
| GET | `/api/dashboard` | ダッシュボード | ✅ |
| GET | `/api/inventory/stocks` | 数量管理 | ✅ |
| GET | `/api/devices/category/{code}` | 個別管理 | ✅ |
| GET | `/api/devices/{id}` | 端末詳細 | ✅ |
| GET | `/api/devices/search` | 端末検索 | ✅ |
| POST | `/api/devices` | 端末登録 | ✅ |
| POST | `/api/devices/multi/{upload,store}` | CSV登録 | ✅ |
| GET\|POST | `/api/devices/file/{spec,benchmark}` | ファイル管理 | ✅ |
| GET\|POST | `/api/clients` | クライアント管理 | ✅ |
| GET\|POST | `/api/contacts` | 担当者管理 | ✅ |
| GET\|POST | `/api/rental{,/history}` | レンタル管理 | ✅ |
| POST | `/api/rental/multi/{upload,store}` | CSV一括レンタル | ✅ |
| POST | `/api/rental/multi/return/{lendId}` | 返却処理 | ✅ |

---

## 参考資料

### 旧実装の読み込み順序（3-7 販売の場合）
1. `api/app/Http/Controllers/SaleController.php` - ロジック確認
2. `api/resources/views/sale/` - UI 確認
3. `api/routes/web.php` で `sale` ルート定義を確認

### 既存パターン（リサーチ用）
- 一覧 + 検索: `RentalHistoryPage.tsx` + `useRentalHistory.ts`
- 登録フォーム + 422: `RentalCartForm.tsx` + `useStoreRental.ts`
- 多段フロー: `RentalFileForm.tsx` + `useUploadRentalMulti.ts`
- 詳細・操作: `RentalHistoryDetailPage.tsx` + `useReturnDevice.ts`

---

**ブランチ**: `claude/upbeat-maxwell-ft3dhm`
**最新コミット**: `feat: 3-6 レンタル手続きAPIとUI実装`
**次セッション目標**: 3-7（販売）に着手、または 3-8（履歴統合）で軽めに前進
