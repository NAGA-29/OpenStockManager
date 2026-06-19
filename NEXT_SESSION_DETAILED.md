# 次セッション引き継ぎ指示書（OpenStockManager React 移行 3-4 完了後）

## 直近完了内容（このセッション）

### ✅ 3-5 フェーズ 完全完了（全 8 画面）
- クライアント: 一覧 ✅ / 詳細 ✅ / 登録 ✅
- 担当者: 一覧 ✅ / 詳細 ✅ / 登録 ✅
- ファイル: スペック ✅ / ベンチマーク ✅

### ✅ 3-4 CSV 一括登録 完了
- **API**: `POST /api/devices/multi/{upload,store}` 
  - upload: CSV 解析・プレビュー（device_id 自動生成含む）
  - store: プレビュー確認後の一括保存
- **フロント**: `/device/register/multi`（3-state component）
- **Sidebar**: 「登録 > 機材（CSV一括）」追加

### テスト状況
```
API: 272 passed / 1 risky / 3 pre-existing failures
Frontend: build ✅ green
```

---

## 推奨される次のタスク（優先順）

### オプション 1️⃣: 3-3 残（バーコード・検索）
**難度**: 🔴 高 | **API実装**: ❌ 未実装

#### バーコード印刷 (`/devices/:id/barcode`)
- **旧ファイル**: `devices/barcode_print.blade.php`
- **機能**: Device ID からバーコード画像を生成・表示・印刷
- **API必須**:
  - `GET /api/devices/:id/barcode` → バーコード画像（Base64 or URL）
- **技術課題**:
  - バーコードライブラリ（jsbarcode / barcode.js）の選定
  - React でのキャンバス/SVG 描画
  - 印刷スタイルシート対応

#### デバイス検索 (`/devices/search`)
- **旧ファイル**: `devices/search_results.blade.php`
- **機能**: キーワード（device_id/name/serial）+ カテゴリフィルタ
- **API必須**:
  - `GET /api/devices/search?keyword=...&category=...` → デバイス一覧
- **技術課題**:
  - 全文検索ロジック実装
  - ページネーション（旧は 10 件ページング）
  - カメラスキャン（バーコード読み込み）→ React Native or external library 検討

---

### オプション 2️⃣: 3-6（手続き・レンタル）
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

### オプション 3️⃣: 3-7（手続き・販売）
**難度**: 🟠 中 | **API実装**: ❌ 未実装

- レンタルと同様の流れ（カート → 入力 → 確認 → 登録）
- API エンドポイント: `GET/POST /api/sale`, `GET/POST /api/sale/write/:deviceId`
- CSV 一括販売: `/api/sale/multi/{upload,store}`
- 販売履歴: `GET /api/sale/history`, `GET /api/sale/history/:id`

---

## 次セッションでの実装フロー（推奨）

### 手順: 3-3 バーコード・検索 を実装する場合

1. **API 実装（DeviceController）**:
   ```php
   // 1. GET /api/devices/barcode/{deviceId}
   public function barcode(string $deviceId): JsonResponse {
       // Device 取得 → jsbarcode 互換の SVG/URL 返却
       // または Base64 encoded image data
   }
   
   // 2. GET /api/devices/search
   public function search(Request $request): JsonResponse {
       // keyword: device_id/name/serial で LIKE 検索
       // category: 絞り込み可能
       // soft-delete 除外
       // → $devices = Device::where('device_id', 'like', '%...%')...->get()
   }
   ```

2. **フロント hook**:
   ```ts
   // features/devices/useDeviceBarcode.ts
   export function useDeviceBarcode(deviceId: string)
   
   // features/devices/useDeviceSearch.ts
   export function useDeviceSearch(keyword: string, category?: string)
   ```

3. **React コンポーネント**:
   ```
   pages/DeviceDetailPage.tsx → Barcode 印刷ボタン追加
   pages/DeviceSearchPage.tsx → 検索フォーム + 結果テーブル
   router.tsx → /devices/search ルート追加
   Sidebar → 「在庫一覧 > 検索」メニュー追加
   ```

4. **検証**:
   ```bash
   php artisan test  # API テスト追加
   npm run build && npm run typecheck
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
| 3-3 バーコード | 高 | 3-4h | ⭐ |
| 3-3 検索 | 高 | 4-5h | ⭐ |
| 3-6 レンタル | 中高 | 5-6h | ⭐⭐ |
| 3-7 販売 | 中 | 4-5h | |
| 3-1 残（パス変更など） | 低～中 | 2-3h | |

**推奨**: 3-3（バーコード or 検索）を選んで実装。OR 3-6（レンタル）で本格的な機能を構築

---

## 参考資料

### 旧実装の読み込み順序
1. `api/resources/views/devices/barcode_print.blade.php` - UI 確認
2. `api/app/Http/Controllers/DevicesController.php` の `barcodePrint()` - ロジック確認
3. `api/routes/web.php` でのルート定義

### 既存パターン（リサーチ用）
- 単体登録: `DeviceRegisterPage.tsx` + `useRegisterDevice.ts`
- CSV 登録: `DeviceRegisterMultiPage.tsx` + `useDeviceMulti.ts`
- ファイル: `DeviceSpecFilePage.tsx` + `useDeviceSpecFile.ts`

---

**ブランチ**: `claude/upbeat-maxwell-ft3dhm`
**最新コミット**: `docs: 3-4 CSV 一括登録完了を反映`
**次セッション目標**: 3-3 or 3-6 のいずれかを完了
