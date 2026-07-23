# devices シーダーと custom_fields 移行の整合

- 日付: 2026-02-19
- 対象: `database/seeders/DevicesTableSeeder.php`
- 背景: `2026_02_19_000002_update_devices_for_custom_fields` により `devices.os` / `devices.os_ver` が削除され、シード時に `Undefined column` が発生していた。
- 対応: 旧カラムへの挿入を廃止し、`custom_fields` (JSON) に `os` / `os_ver` 相当値を保存するよう変更。
- 検証: `docker compose exec api php artisan migrate:fresh --seed` が成功することを確認。
