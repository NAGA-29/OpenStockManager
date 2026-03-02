# 改善タスクリスト

このドキュメントは `docs/code_analysis_report.md` に基づいた改善タスクを管理します。

## 高優先度：パフォーマンス

- [x] **Eager Loading適用** - `RentalHistsController::getAllHistory()` ✅
  - 対象: `app/Http/Controllers/RentalHistsController.php:439`
  - 問題: 関連データ（clients, personnels, user）を個別に取得しているためN+1問題が発生
  - 解決: `with(['clients', 'personnels', 'user'])` でEager Loadingを適用

- [ ] **N+1問題修正** - `DevicesController::deviceIndividual()`
  - 対象: `app/Http/Controllers/DevicesController.php:366-368`
  - 問題: ループ内で `Client::find()` を個別に呼び出し
  - 解決: リレーションのEager Loadingで一括取得

## 中優先度：保守性・拡張性

- [ ] **DeviceServiceクラス作成**
  - CSV解析、画像処理、DB操作ロジックをコントローラーから分離
  - 対象メソッド: `storeDevice()`, `storeDeviceMulti()`, `confirmMulti()`, `updateDevice()`

- [ ] **RentalServiceクラス作成**
  - レンタル処理ロジックをコントローラーから分離
  - 対象メソッド: `storeWithCart()`, `storeWithFile()`, `return()`

- [ ] **重複メソッドの統合** - `DevicesController`
  - 対象: `stb()`, `camera()`, `tablet()`, `signage()`, `otherDevice()`
  - 解決: `deviceList($type)` メソッドに統合し、ルーティングを `Route::get('/devices/{type}', ...)` に変更

## 推奨：テスト

- [ ] **DeviceRegistrationTest作成**
  - 端末登録の正常系テスト
  - 端末登録の異常系テスト（バリデーションエラー、重複IDなど）

- [ ] **RentalProcessTest作成**
  - レンタル処理の正常系テスト
  - レンタル処理の異常系テスト（存在しない端末、レンタル中端末など）

## 改善：UX

- [ ] **CSVエラーメッセージの改善**
  - 対象: `DevicesController::confirmMulti()`
  - 問題: エラー発生時に具体的な行番号・項目名が表示されない
  - 解決: 「X行目の'項目名'に誤りがあります」形式でエラー表示

---

## 進捗状況

| カテゴリ | 完了 | 残り |
|---------|------|------|
| パフォーマンス | 1 | 1 |
| 保守性・拡張性 | 0 | 3 |
| テスト | 0 | 2 |
| UX | 0 | 1 |
| **合計** | **1** | **7** |
