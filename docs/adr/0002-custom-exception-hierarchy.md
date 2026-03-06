# ADR-0002: カスタム例外クラス階層の導入

- **ステータス**: 承認済み
- **決定日**: 2026-03-05
- **決定者**: 開発チーム

---

## コンテキスト

Laravel 11 ベースのバックエンドにおいて、例外処理に以下の問題が存在していた。

### 1. 汎用 `Exception` に依存した例外設計

コントローラー・サービス層のすべての例外が PHP 組み込みの `Exception` クラスで投げられていた。

```php
// 改善前: 何の例外かコードを読まないとわからない
throw new Exception(__('messages.device_currently_rented', ['device_id' => $record['device_id']]));
throw new Exception(__('messages.image_analysis_failed'));
```

この設計では、catch ブロックで例外の種類を判別できないため、**ビジネスロジックエラー（貸出中・販売済みなど）** と **システムエラー（DB障害・外部API障害）** を同じ方法で処理せざるを得なかった。

### 2. Null ポインタ参照バグ（`StoreRentalCartRequest`）

`withValidator` メソッド内で、Eloquent `first()` が `null` を返した場合にプロパティアクセスが発生し、fatal error になる実装があった。

```php
// 改善前: $device が null の場合に fatal error
foreach ($this->input('deviceIds') as $deice_id) {     // タイポも含む
    $device = Device::where('device_id', $deice_id)->first();
    if ($device->sale_id != '') { ... }  // $device === null のとき fatal error
```

### 3. `ImageProcessor` のサイレント失敗

`getimagesize()` が `false` を返す場合や `getID3` が例外を投げる場合に、エラーの原因が呼び出し元に伝わらない設計になっていた。コントローラー側では `if (!$img_info)` という不完全なチェックで対応していた。

### 4. ログの精度不足

catch ブロックで `error_class` が記録されていない箇所や、デバイス検証エラー（ユーザー起因）とシステムエラーを同じ重大度でログに記録していた箇所があった。

---

## 決定

カスタム例外クラスの階層を導入し、例外の種別・文脈情報・ハンドリング方針を明示する。

### 例外クラス構成

```
app/Exceptions/
├── AppException.php                              ← 基底クラス (abstract, RuntimeException 継承)
├── Domain/
│   ├── DeviceException.php                       ← デバイス系例外の基底 (abstract)
│   └── Device/
│       ├── DeviceNotFoundException.php
│       ├── DeviceAlreadySoldException.php
│       ├── DeviceCurrentlyRentedException.php
│       ├── DeviceDefectiveException.php
│       └── DeviceDuplicateException.php
└── Infrastructure/
    ├── ImageProcessingException.php
    └── CsvImportException.php                    ← 行番号・行データをコンテキストとして保持
```

### `AppException` の設計方針

```php
abstract class AppException extends RuntimeException
{
    public function __construct(string $message, private readonly array $context = [], ?\Throwable $previous = null)
    { ... }

    public function getContext(): array { return $this->context; }
}
```

- `$context` でログ出力や Handler での処理に必要なメタデータ（`device_id` など）を保持
- `$previous` で例外チェーンを維持し、根本原因を失わない

### ファクトリメソッドパターン

各具象クラスはインスタンス生成をファクトリメソッドに集約し、メッセージ生成とコンテキスト設定を統一する。

```php
// 改善後: 意図が明確で、メッセージ生成ロジックがカプセル化される
throw DeviceCurrentlyRentedException::forDevice($record['device_id']);

// DeviceCurrentlyRentedException::forDevice() の内部
return new self(
    __('messages.device_currently_rented', ['device_id' => $deviceId]),
    ['device_id' => $deviceId]
);
```

### catch ブロックの階層化

ドメイン例外（ユーザー起因）とシステム例外を分離してキャッチする。

```php
// 改善後: 例外の種別に応じた処理が可能
} catch (DeviceNotFoundException | DeviceAlreadySoldException | DeviceCurrentlyRentedException | DeviceDefectiveException $err) {
    // ユーザー向けメッセージをそのまま表示（ログ不要）
    return redirect()->back()->with('error_message', $err->getMessage());
} catch (Exception $err) {
    // システムエラーはログに記録し、一般的なエラーメッセージを返す
    Log::channel('error')->error('...', ['error_class' => get_class($err), ...]);
    return redirect()->back()->with('error_message', __('messages.csv_parse_failed'));
}
```

### `ImageProcessor` の例外伝搬

`getimagesize()` の失敗と `getID3` の例外を `ImageProcessingException` に変換して伝搬する。これにより呼び出し元（コントローラー）での不完全な `if (!$img_info)` チェックが不要になる。

### `Handler.php` での安全網

コントローラーでキャッチされなかった `DeviceException` および `ImageProcessingException` に対して、フォールバックとして `Handler::render()` でリダイレクトとログ出力を行う。また `DeviceException` と `CsvImportException` は Sentry への報告対象から除外する（ユーザー起因のエラーであるため）。

### `StoreRentalCartRequest` の null チェック修正

```php
// 改善後
foreach ($this->input('deviceIds', []) as $device_id) {  // デフォルト [] でnull安全
    $device = Device::where('device_id', $device_id)->first();
    if ($device === null) {                               // null チェックを追加
        $validator->errors()->add('deviceIds', '指定されたデバイスが存在しません');
        continue;                                         // 後続チェックをスキップ
    }
    ...
}
```

---

## 検討した代替案

### 案A: Laravel の `ValidationException` を活用する

デバイス状態チェックを Form Request バリデーション内で完結させ、`ValidationException` を使う方法。

**却下理由**: デバイス状態チェック（貸出中・販売済みなど）はビジネスロジックであり、Form Request のバリデーション層に配置するのは責務の分離として適切でない。また CSV 一括処理ではループ内で複数レコードを順次検証するため、Laravel の標準バリデーション機構にそのまま乗せることが難しい。

### 案B: Result 型（成功/失敗を値として返す）を導入する

例外を使わず、`Result<T, E>` のような型で処理結果を表現する方法。

**却下理由**: PHP / Laravel エコシステムの標準的なパターンではなく、既存コードベースとの整合性を損なう。導入コストに対して得られるメリットが現時点の規模では過剰となる。

### 案C: 既存の汎用 `Exception` のまま、ログのみ改善する

例外クラスは変えず、catch ブロックのログ記述を充実させる方法。

**却下理由**: 根本的な問題（例外の種別判別不能、null ポインタバグ）が解消されない。また将来の機能追加時に同様の設計が踏襲されるリスクが残る。

---

## 結果・トレードオフ

### 改善された点

| 観点 | 改善前 | 改善後 |
|------|--------|--------|
| 例外の識別 | `get_class($e)` でしか判別不可 | 型による catch ブロック分岐が可能 |
| ビジネスエラーとシステムエラーの分離 | 同一 catch で処理 | 別 catch で処理を分離 |
| ユーザー向けメッセージ | 汎用エラー or 例外メッセージ直出力 | ドメイン例外はファクトリで生成された適切なメッセージ |
| Sentry ノイズ | ユーザー起因エラーも Sentry に送信 | `dontReport` で除外 |
| Null ポインタ安全性 | fatal error の可能性あり | null チェックで安全に処理 |
| `ImageProcessor` の失敗原因 | `null` を返すのみ | 例外チェーンで根本原因を保持 |

### 残存する課題・今後の方針

- **`CsvImportException` の行番号活用**: 現在は例外クラスに行番号保持の仕組みを用意したが、コントローラー側での使用はまだ未導入。CSV処理のリファクタリング時に `CsvImportException::forRow($rowNumber, ...)` を活用する。
- **外部API例外の分離**: `ClientsController` の Guzzle HTTP 呼び出しは依然として汎用 `Exception` で処理している。`Infrastructure/ExternalApiException` の追加と適用は別タスクとする。
- **メール通知失敗の明示化**: `ReturnDeadlineNotificationService` の SendGrid 失敗がサイレントになっている点は `Notification/EmailNotificationException` を別途導入して対処する。

---

## 関連ファイル

**新規作成**
- `app/Exceptions/AppException.php`
- `app/Exceptions/Domain/DeviceException.php`
- `app/Exceptions/Domain/Device/DeviceNotFoundException.php`
- `app/Exceptions/Domain/Device/DeviceAlreadySoldException.php`
- `app/Exceptions/Domain/Device/DeviceCurrentlyRentedException.php`
- `app/Exceptions/Domain/Device/DeviceDefectiveException.php`
- `app/Exceptions/Domain/Device/DeviceDuplicateException.php`
- `app/Exceptions/Infrastructure/ImageProcessingException.php`
- `app/Exceptions/Infrastructure/CsvImportException.php`

**変更**
- `app/Exceptions/Handler.php` — フォールバックハンドリングと `dontReport` の追加
- `app/Http/Controllers/DevicesController.php` — `ImageProcessingException` を個別 catch
- `app/Http/Controllers/RentalHistsController.php` — ドメイン例外への置き換えと catch 分離
- `app/Http/Controllers/SalesHistsController.php` — 同上
- `app/Http/Requests/StoreRentalCartRequest.php` — null チェック修正・タイポ修正
- `app/Services/Image/ImageProcessor.php` — 例外チェーンによる失敗伝搬
