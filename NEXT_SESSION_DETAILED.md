# 次セッション引き継ぎ指示書（OpenStockManager React 移行 3-3 完了後）

## 直近完了内容（過去セッション含む）

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
- **バーコード**: 既存 `GET /api/devices/:id` を再利用（API 追加なし）

### テスト状況
```
API: 277 passed / 1 risky / 3 pre-existing failures（Blade Vite manifest）
Frontend: build / typecheck / lint ✅ green
```

---

## 推奨される次のタスク（優先順）

### ⭐ オプション 1️⃣: 3-6（手続き・レンタル）【最有力】
**難度**: 🟠 中～高 | **API実装**: ❌ 未実装

#### レンタル手続き (`/rental` → `/rental/cart` → 確認)
- **旧ファイル**: `rental/index.blade.php`・`rental/rental.blade.php`
- **機能**: 
  1. レンタル対象端末を検索・選択（カート）
  2. 借用者・返却期限を入力
  3. 確認・登録
- **API必須**:
  - `GET /api/rental` → レンタル一覧（借用中・返却予定）
  - `POST /api/rental/store` → レンタル登録
  - `GET /api/rental/checkout/:deviceId` → 貸出情報（詳細）
  - `POST /api/rental/multi/return/:lendId` → 一括返却
- **フロント複雑性**: 中程度（カート UI・日付ピッカー）

#### CSV 一括レンタル (`/rental/rental_with_file_confirm`)
- **API必須**:
  - `POST /api/rental/multi/upload` → CSV パース・プレビュー
  - `POST /api/rental/multi/store` → 確認後の一括登録

#### レンタル履歴 (`/rental/history` → `/rental/history/:id`)
- **API必須**:
  - `GET /api/rental/history` → レンタル履歴一覧
  - `GET /api/rental/history/:id` → 履歴詳細

---

### オプション 2️⃣: 3-7（手続き・販売）
**難度**: 🟠 中 | **API実装**: ❌ 未実装

- レンタルと同様の流れ（カート → 入力 → 確認 → 登録）
- API エンドポイント: `GET/POST /api/sale`, `GET/POST /api/sale/write/:deviceId`
- CSV 一括販売: `/api/sale/multi/{upload,store}`
- 販売履歴: `GET /api/sale/history`, `GET /api/sale/history/:id`

### オプション 3️⃣: 3-8（履歴）または 3-9（設定・admin）
**難度**: 🟠 中 | **API実装**: ❌ 未実装
- 3-8: レンタル/販売の履歴一覧・詳細（読取中心で比較的着手しやすい）
- 3-9: ユーザー管理・カテゴリ・カスタムフィールド（admin ミドルウェア適用が必要＝1-6 を実質達成する回）

---

## 次セッションでの実装フロー（推奨）

### 手順: 3-6 レンタル を実装する場合

1. **まず旧実装を読む**:
   ```bash
   # コントローラ（ロジック確認）
   api/app/Http/Controllers/RentalHistsController.php
   # Blade（UI 確認）
   api/resources/views/rental/index.blade.php
   api/resources/views/rental/rental.blade.php
   # ルート（エンドポイント確認）
   grep "rental" api/routes/web.php
   ```

2. **API 実装（新規 `Api\RentalController`）**:
   - `GET /api/rental` → レンタル一覧（貸出中・返却予定）
   - `POST /api/rental/store` → レンタル登録（device_id 群 + client + 返却期限）
   - `POST /api/rental/multi/return/:lendId` → 一括返却
   - 旧 Request クラス（`StoreRentalRequest` 等）を参照し Api 版で 422 JSON 化
   - `routes/api.php` の `auth:sanctum` グループに登録

3. **フロント**:
   ```
   features/rental/useRental.ts（一覧）/ useStoreRental.ts（mutation）
   pages/RentalPage.tsx（一覧 + カート導線）
   pages/RentalCartPage.tsx（選択端末 + 借用者 + 返却期限入力 → 確認 → 登録）
   router.tsx → /rental・/rental/cart 追加
   Sidebar「手続き > レンタル」は既に導線あり（リンク先実装するだけ）
   ```

4. **検証**:
   ```bash
   cd api && php artisan test          # RentalApiTest 追加
   cd frontend && npm run typecheck && npm run build && npm run lint
   ```

---

## 重要な開発上の注意

### CSS 共有パターン
- `inventory.css`: device-card, device-info-table, status-icon など
- `clients.css`: clients-toolbar, device-note など
- `register.css`: register-field, register-actions など

→ 新しいコンポーネントは **既存 CSS を再利用** してスタイルを統一

### API 設計パターン
- **リスト取得**: `{ data: [{ ... }, ...] }`
- **詳細取得**: `{ data: { ... } }`
- **作成/更新成功**: `201`・`200` + data
- **バリデーション失敗**: `422` + `{ message?, errors? }`

### Blade 維持
- web.php ルートは削除しない
- 旧ビューの削除は Phase 4-1（全画面移行完了後）

---

## 確認チェックリスト（次セッション開始時）

```bash
# ブランチ確認
git branch  # → claude/upbeat-maxwell-ft3dhm であること

# 最新状態確認
git log --oneline -5

# テスト環境確認
cd api && composer install && php artisan test 2>&1 | grep "Tests:"
# → 272 passed / 1 risky であること

# フロント ビルド確認
cd ../frontend && npm run build 2>&1 | tail -3
# → built in X.XXs で成功すること
```

---

## 選択ガイド

| タスク | 難度 | 所要時間 | 推奨 |
|-------|------|--------|------|
| 3-6 レンタル | 中高 | 5-6h | ⭐⭐ |
| 3-7 販売 | 中 | 4-5h | ⭐ |
| 3-8 履歴（読取中心） | 中 | 3-4h | ⭐ |
| 3-9 設定（admin） | 中 | 4-5h | |
| 3-1 残（パス変更など） | 低～中 | 2-3h | |

**推奨**: 3-6（レンタル）で本格的な手続きフローを構築。軽めに進めるなら 3-8（履歴）が読取中心で着手しやすい。

---

## 参考資料

### 旧実装の読み込み順序（3-6 レンタルの場合）
1. `api/resources/views/rental/index.blade.php` / `rental/rental.blade.php` - UI 確認
2. `api/app/Http/Controllers/RentalHistsController.php` - ロジック確認
3. `api/routes/web.php` で `rental` ルート定義を確認

### 既存パターン（リサーチ用）
- 一覧 + 検索: `ClientsPage.tsx` + `useClients.ts` / `DeviceSearchPage.tsx` + `useDeviceSearch.ts`
- 登録フォーム + 422: `ContactRegisterPage.tsx` + `useRegisterContact.ts`
- 多段フロー（アップロード→確認→完了）: `DeviceRegisterMultiPage.tsx` + `useDeviceMulti.ts`
- ファイルアップロード: `DeviceSpecFilePage.tsx` + `useDeviceSpecFile.ts`
- 印刷/外部ライブラリ: `DeviceBarcodePage.tsx`（jsbarcode + `@media print`）

### API パターン早見表
- 一覧: `{ data: [...] }` / ページング時は `+ meta`
- 詳細: `{ data: {...} }`、未知 ID は `firstOrFail()` で 404
- 作成: 201 + `{ data: {...} }`、バリデーション失敗は 422 + `{ message, errors }`
- 検索: `Keyword` trait（`extractKeywords` + `mb_convert_kana`）で複数キーワード化
- ルート順: パラメータ付き（`/devices/{id}`）より具体パス（`/devices/search`）を**先に**定義

---

**ブランチ**: `claude/upbeat-maxwell-ft3dhm`
**最新コミット**: `docs: 3-3 バーコード・検索完了を反映`
**次セッション目標**: 3-6（レンタル）に着手、または 3-8（履歴）で軽めに前進

## Phase 3 進捗スナップショット（2026-06-19 時点）
- 3-1 認証: ◐（ログインのみ／残 6 画面は API 未実装）
- 3-2 ダッシュボード: ☑
- 3-3 在庫: ☑（数量／個別／詳細／バーコード／検索）
- 3-4 端末登録: ◐（単体＋CSV一括 完了／画像のみ残）
- 3-5 データ: ☑（クライアント・担当者・ファイル 全 8 画面）
- 3-6 レンタル / 3-7 販売 / 3-8 履歴 / 3-9 設定 / 3-10 モーダル / 3-11 エラー: ☐
- API テスト: 277 passed / 1 risky / 3 pre-existing failures（Blade Vite manifest）
