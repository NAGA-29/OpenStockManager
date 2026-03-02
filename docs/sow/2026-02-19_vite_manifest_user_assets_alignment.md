# Vite manifest とユーザー管理画面アセットの整合

- 日付: 2026-02-19
- 対象: `vite.config.js`
- 背景: `resources/views/user/index.blade.php` で `@vite('resources/css/edit-user-dialog.css')` および `@vite('resources/js/user/edit-user-modal.js')` を参照しているが、Vite input 未登録で manifest に出力されず `Unable to locate file in Vite manifest` が発生していた。
- 対応: Vite の `laravel` plugin `input` に上記 2 アセットを追加。
- 検証: `npm run build` で `public/build/manifest.json` に両アセットが出力されることを確認。
