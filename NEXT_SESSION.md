# 次セッション引き継ぎ指示書（OpenStockManager React 移行 Phase 3-5 完了後）

## 完了内容（このセッションで実装）

### タスク 1: 担当者登録 (3-5)
**マージ状況**: ✅ 完了・push 済み

- **API**: `StoreContactApiRequest`・`Api\ContactController@store` 追加
- **ルート**: `POST /api/contacts` 登録
- **テスト**: `ContactApiTest` に store 用ケース 6 件（全て green）
- **フロント**: `useRegisterContact` mutation + `ContactRegisterPage`（select 選択肢、422 フィールド表示）
- **ナビ**: Sidebar「登録 > 担当者」、ContactsPage「新規登録」ボタン
- **検証**: `php artisan test` → 272 passed / 1 risky、`npm run build` green

### タスク 2: スペック・ベンチマークファイル (3-5)
**マージ状況**: ✅ 完了・push 済み

- **API**: `UploadSpecFileApiRequest`・`UploadBenchmarkFileApiRequest` + 4 メソッド（`getSpecFile`/`uploadSpecFile`/`getBenchmarkFile`/`uploadBenchmarkFile`）
  - `GET /api/devices/file/{spec,benchmark}` → ファイル情報（filename/size/updated_at）か null
  - `POST /api/devices/file/{spec,benchmark}` → ファイル置き換え・成功時 200
- **フロント**: 
  - `useDeviceSpecFile`・`useDeviceBenchmarkFile`（Query + Mutation）
  - `DeviceSpecFilePage`・`DeviceBenchmarkFilePage`（ファイル表示・アップロード UI）
  - `device-files.css`（スタイル）
- **ナビ**: Sidebar「データ一覧 > スペックデータ／ベンチマーク」追加
- **検証**: API テスト・フロント build 共に green

### Phase 3-5 全体状況
- **達成**: 8 画面全て React 化完了
  - クライアント: 一覧 ✅ / 詳細 ✅ / 登録 ✅
  - 担当者: 一覧 ✅ / 詳細 ✅ / 登録 ✅
  - ファイル: スペック ✅ / ベンチマーク ✅
- **API テスト**: 272 passed / 1 risky / 3 pre-existing failures（Blade）

---

## 推奨される次のタスク

### 選択肢 A: 3-4 残（CSV 一括登録・確認）
**難度**: 高 | **API実装**: 未確認
- `POST /api/devices/multi/upload`（CSV アップロード＝パース＆プレビュー）
- `POST /api/devices/multi/store`（確定保存）
- フロント: ファイル選択 → プレビュー表示 → 一括登録

### 選択肢 B: 3-3 残（バーコード・検索）
**難度**: 高 | **API実装**: 未確認
- `GET /api/devices/barcode/{deviceId}`（バーコード画像生成）
- `GET /api/devices/search`（端末検索＝キーワード/カテゴリ）
- フロント: バーコード印刷ページ・検索結果ページ

### 選択肢 C: 3-6（手続き・レンタル）
**難度**: 中 | **API実装**: 要確認
- `GET /api/rental` / `POST /api/rental/store`（レンタルカート処理）
- フロント: レンタル一覧・カート・確認・返却

---

## 開発環境確認（次セッション最初にする）

```bash
cd /home/user/OpenStockManager

# ブランチ確認
git branch

# 指定ブランチへ切り替え（必要なら）
git checkout claude/upbeat-maxwell-ft3dhm

# コミット履歴確認（最新 3 件）
git log --oneline -3

# API テスト実行
cd api && php artisan test 2>&1 | grep "Tests:"

# フロント ビルド確認
cd ../frontend && npm run build 2>&1 | tail -5
```

## 次セッションの作業フロー

1. **対象を 1 つ選ぶ**: 上記 A/B/C から 1 つを選択
2. **API 実装済みか確認**: 
   - A の場合: `grep -r "multi/upload\|multi/store" api/app/Http/Controllers/`
   - B の場合: `grep -r "devices/search\|devices/barcode" api/routes/`
   - C の場合: `grep -r "rental" api/routes/api.php`
3. **API が未実装なら API 化から開始**
4. **フロント実装**
5. **検証**: `php artisan test` + `npm run build && npm run typecheck`
6. **コミット**: `feat(react-migration): <画面名> を React 化 (3-X)`
7. **ドキュメント更新**: `docs/react-laravel-migration.md` §2/§3/§5

## 既知の制約・注意

- **Blade ビュー**: 旧 `resources/views` はまだ削除しない（web.php ルートは動作維持）
- **Package Lock**: `api/package-lock.json` をコミットに含めない
- **ESLint**: v10 は .eslintrc.cjs をサポート外。lint チェック時エラーが出ても「ビルド成功なら OK」（設定は次フェーズで対応）
- **TypeScript**: baseUrl 弃用警告は出るが build には影響なし

## 参考：最新の進捗

- **フェーズ**: Phase 3（画面移行）・3-5 完全完了
- **全体進捗**: 3-1(◐) / 3-2(☑) / 3-3(◐) / 3-4(◐) / 3-5(☑) / 3-6(☐) 以降
- **API テスト**: 272 passed（接触 3-5 で +9、3-4 で +4、3-3 で +4）
- **フロント**: Router に 12 ルート登録、Sidebar に対応メニュー完備

---

**ブランチ**: `claude/upbeat-maxwell-ft3dhm`
**直近コミット**: `docs: 3-5 全画面完了を反映（チェックリスト・画面対応表・申し送り）`
