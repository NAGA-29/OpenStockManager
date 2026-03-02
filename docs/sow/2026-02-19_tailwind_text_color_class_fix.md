# Tailwind 文字色クラス適用不具合の修正

- 日付: 2026-02-19
- 対象: `resources/views/layouts/app.blade.php`
- 背景: ヘッダー文字色変更で `text-indigo` / `text-primary` を利用していたが、前者は無効なTailwindクラス、後者は本プロジェクト未定義のため色が反映されなかった。
- 対応: `Device` の文字色クラスを Tailwind 標準の `text-blue-500` に変更。
- 検証: `npm run build` 成功を確認。
