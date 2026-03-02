# DeviceManager リファクタリング実装計画

## 「なんちゃってクリーンアーキテクチャ」の適用

### 参考記事

- [5年間 Laravel を使って辿り着いた，全然頑張らない「なんちゃってクリーンアーキテクチャ」という落としどころ](https://zenn.dev/yumemi_inc/articles/ce7d09eb6d8117)

---

## 1. 背景と目的

### 1.1 記事の要旨

mpyw氏が提唱する「なんちゃってクリーンアーキテクチャ」は、以下の3つの原則に基づく：

1. **DDDや"真の"クリーンアーキテクチャはオーバースペック** - Web業界の大抵の現場では導入しても全員がついてこれるとは限らない
2. **`app/UseCases` ディレクトリを切り、ドメインごとに単一責務なクラスを置く** - シンプルだが効果的
3. **ActiveRecord指向のフレームワークでRepositoryパターンを無理に導入しない** - UseCaseでEloquent Modelの機能を使うことを恐れない

核心は「ビュー ⇄ FormRequest ⇄ Controller ⇄ UseCase ⇄ Model」という責務の流れを作ることにある。

### 1.2 現状の課題

DeviceManagerプロジェクトでは以下の問題が確認されている（`docs/code_analysis_report.md` 参照）：

| 課題 | 詳細 |
|------|------|
| **Fat Controller** | ビジネスロジック（CSV解析、画像処理、DB操作、バリデーション）がコントローラーに集中 |
| **コードの重複** | `getPersonnel()` が `RentalHistsController` と `SalesHistsController` に重複。CSV解析ロジックも同様 |
| **テスト困難** | コントローラーにロジックが密結合しており、ユニットテストが書きにくい |
| **N+1問題** | 一部修正済みだが `DevicesController::deviceIndividual()` に残存 |

### 1.3 目指す姿

```
リクエスト → FormRequest(バリデーション) → Controller(橋渡し) → UseCase(ビジネスロジック) → Model(データ操作)
                                                ↓
                                           レスポンス/ビュー
```

Controller は **FormRequest と UseCase の橋渡しをするだけ** の薄いクラスにする。

---

## 2. 目標ディレクトリ構成

```
app/
├── Console/
├── Enums/
├── Exceptions/
├── Http/
│   ├── Controllers/           # 薄いController（橋渡しのみ）
│   │   ├── Auth/
│   │   ├── DevicesController.php
│   │   ├── RentalHistsController.php
│   │   ├── SalesHistsController.php
│   │   ├── PersonnelsController.php
│   │   ├── ClientsController.php
│   │   ├── UserController.php
│   │   └── MailingController.php
│   ├── Middleware/
│   ├── Requests/              # FormRequest（バリデーション専任）
│   └── UseCases/              # 海面レベルUseCase（Controllerから直接利用）
│       ├── Device/
│       │   ├── StoreDeviceUseCase.php
│       │   ├── StoreDeviceMultiUseCase.php
│       │   ├── ConfirmDeviceMultiUseCase.php
│       │   ├── UpdateDeviceUseCase.php
│       │   ├── SearchDeviceUseCase.php
│       │   ├── GetDeviceListUseCase.php
│       │   ├── GetDeviceDetailUseCase.php
│       │   └── UploadFileUseCase.php
│       ├── Rental/
│       │   ├── StoreRentalWithCartUseCase.php
│       │   ├── StoreRentalWithFileUseCase.php
│       │   ├── UploadRentalFileUseCase.php
│       │   ├── ReturnDeviceUseCase.php
│       │   ├── ReturnDeviceMultiUseCase.php
│       │   ├── GetRentalHistoryUseCase.php
│       │   ├── GetRentalDetailUseCase.php
│       │   └── EditRentalHistoryUseCase.php
│       ├── Sale/
│       │   ├── StoreWithCartUseCase.php
│       │   ├── StoreWithFileUseCase.php
│       │   ├── UploadSaleFileUseCase.php
│       │   ├── GetSaleHistoryUseCase.php
│       │   ├── GetSaleDetailUseCase.php
│       │   └── EditSaleHistoryUseCase.php
│       └── Common/
│           └── GetPersonnelUseCase.php
├── Lib/
├── Logging/
├── Models/
├── Notifications/
├── Providers/
├── Services/                  # 既存Serviceはそのまま維持
│   ├── Image/
│   │   └── ImageProcessor.php
│   ├── Interfaces/
│   │   └── MessageInterface.php
│   ├── Messaging/
│   │   ├── Chatwork.php
│   │   └── Slack.php
│   └── ReturnDeadlineNotificationService.php
├── Traits/
└── Utils/
```

### 設計方針

- **海面レベル UseCase** → `app/Http/UseCases/` に配置。Controllerから直接呼ばれるもの
- 現時点では**海中レベル UseCase**（`app/UseCases/`）は不要。必要になったら追加する
- **既存の `app/Services/` はそのまま維持**。ImageProcessor等はUseCaseから利用する
- **1 UseCase = 1 `__invoke()` メソッド**。クラス名は動詞形で統一する
- **Repositoryパターンは導入しない**。UseCase内でEloquent Modelを直接使う

---

## 3. 実装フェーズ

### Phase 1: 基盤整備（影響範囲: 小）

既存の動作に変更を加えず、UseCase導入の下地を作る。

#### 1-1. 重複コードの統合

**対象:** `RentalHistsController::getPersonnel()` と `SalesHistsController::getPersonnel()` の重複

**方針:**
- `app/Http/UseCases/Common/GetPersonnelUseCase.php` を作成
- 両Controllerから共通UseCaseを呼び出す

```php
// app/Http/UseCases/Common/GetPersonnelUseCase.php
namespace App\Http\UseCases\Common;

use App\Models\Personnel;

class GetPersonnelUseCase
{
    public function __invoke(string $clientId): array
    {
        $personnels = Personnel::where('client_id', $clientId)->get();

        if ($personnels->isEmpty()) {
            return ['success' => 0];
        }

        return [
            'success' => 1,
            'data' => $personnels->toArray(),
        ];
    }
}
```

```php
// Controller側（両方共通）
public function getPersonnel(Request $request, GetPersonnelUseCase $useCase)
{
    return response()->json($useCase($request->personnel_id));
}
```

#### 1-2. FormRequestの整備

**対象:** コントローラー内で `Validator::make()` を直接呼んでいる箇所

| Controller | メソッド | 対応 |
|-----------|---------|------|
| `DevicesController` | `confirmMulti()` | 新規FormRequest作成 `ConfirmDeviceMultiRequest` |
| `DevicesController` | `searchDevice()` | 新規FormRequest作成 `SearchDeviceRequest` |
| `DevicesController` | `specUpload()` | 新規FormRequest作成 `UploadSpecFileRequest` |
| `DevicesController` | `benchmarkUpload()` | 新規FormRequest作成 `UploadBenchmarkFileRequest` |
| `SalesHistsController` | `upload()` | 新規FormRequest作成 `StoreSaleFileRequest` |
| `RentalHistsController` | `editRentalHistory()` | 新規FormRequest作成 `EditRentalHistoryRequest` |
| `RentalHistsController` | `return()` | 新規FormRequest作成 `ReturnDeviceRequest` |

---

### Phase 2: DevicesController のリファクタリング（影響範囲: 中）

最もFat Controllerが顕著な `DevicesController` から着手する。

#### 2-1. 機材登録 UseCase の抽出

**Before（現状）:** `DevicesController::storeDevice()` (60行超)
- DB::beginTransaction
- Device::create
- ImageProcessor呼び出し
- Content::create
- Storage操作
- DB::commit / rollBack
- リダイレクト

**After（目標）:**

```php
// Controller
public function storeDevice(StoreDeviceRequest $request, StoreDeviceUseCase $useCase)
{
    $useCase($request->safe()->toArray(), $request->file('device_image'));

    return redirect()->back()
        ->with('success_message', __('messages.registration_completed'));
}
```

```php
// app/Http/UseCases/Device/StoreDeviceUseCase.php
class StoreDeviceUseCase
{
    public function __construct(
        private ImageProcessor $imageProcessor,
    ) {}

    public function __invoke(array $validated, ?UploadedFile $image = null): Device
    {
        return DB::transaction(function () use ($validated, $image) {
            $device = Device::create([...]);

            if ($image) {
                $this->storeDeviceImage($image, $device->device_id);
            }

            return $device;
        });
    }

    private function storeDeviceImage(UploadedFile $image, string $deviceId): void
    {
        // 画像処理・Content作成・Storage保存
    }
}
```

#### 2-2. 機材更新 UseCase の抽出

**対象:** `DevicesController::updateDevice()`

```php
// app/Http/UseCases/Device/UpdateDeviceUseCase.php
class UpdateDeviceUseCase
{
    public function __construct(
        private ImageProcessor $imageProcessor,
    ) {}

    public function __invoke(array $validated, ?UploadedFile $image = null): Device
    {
        return DB::transaction(function () use ($validated, $image) {
            $device = Device::where('device_id', $validated['device_id'])->firstOrFail();
            $device->fill([...])->save();

            $this->syncImages($device, $validated['imageList'] ?? [], $image);

            return $device;
        });
    }
}
```

#### 2-3. CSV一括登録 UseCase の抽出

**対象:** `DevicesController::confirmMulti()` + `DevicesController::storeDeviceMulti()`

```php
// app/Http/UseCases/Device/ConfirmDeviceMultiUseCase.php
class ConfirmDeviceMultiUseCase
{
    public function __invoke(UploadedFile $file): array
    {
        // CSV解析 → バリデーション → デバイスデータ配列を返す
    }
}

// app/Http/UseCases/Device/StoreDeviceMultiUseCase.php
class StoreDeviceMultiUseCase
{
    public function __invoke(array $devices): void
    {
        DB::transaction(function () use ($devices) {
            foreach ($devices as $deviceData) {
                Device::create([...]);
            }
        });
    }
}
```

#### 2-4. 検索 UseCase の抽出

**対象:** `DevicesController::searchDevice()`

```php
// app/Http/UseCases/Device/SearchDeviceUseCase.php
class SearchDeviceUseCase
{
    public function __invoke(string $word, ?string $deviceType = null): LengthAwarePaginator
    {
        // キーワード抽出 → クエリ構築 → ページネーション結果を返す
    }
}
```

#### 2-5. デバイス一覧・詳細 UseCase の抽出

**対象:** `DevicesController::deviceList()`, `DevicesController::deviceIndividual()`

```php
// app/Http/UseCases/Device/GetDeviceListUseCase.php
class GetDeviceListUseCase
{
    public function __invoke(string $deviceType): array
    {
        // デバイス一覧 + カウント情報を返す
    }
}

// app/Http/UseCases/Device/GetDeviceDetailUseCase.php
class GetDeviceDetailUseCase
{
    public function __invoke(string $deviceId): array
    {
        // デバイス詳細 + 日付フォーマット済みデータを返す
    }
}
```

---

### Phase 3: RentalHistsController のリファクタリング（影響範囲: 中）

#### 3-1. カートレンタル UseCase の抽出

**対象:** `RentalHistsController::storeWithCart()`

```php
// app/Http/UseCases/Rental/StoreRentalWithCartUseCase.php
class StoreRentalWithCartUseCase
{
    public function __invoke(array $validated): RentalHist
    {
        return DB::transaction(function () use ($validated) {
            $rental = RentalHist::create([...]);

            foreach ($validated['deviceIds'] as $deviceId) {
                Device::where('device_id', $deviceId)->update(['lending_now' => $validated['lend_id']]);
                $rental->devices()->attach($deviceId, ['checkout_at' => $validated['checkout_at']]);
            }

            return $rental;
        });
    }
}
```

#### 3-2. CSVレンタル UseCase の抽出

**対象:** `RentalHistsController::upload()` + `RentalHistsController::storeWithFile()`

```php
// app/Http/UseCases/Rental/UploadRentalFileUseCase.php
class UploadRentalFileUseCase
{
    public function __invoke(UploadedFile $file, array $validated): array
    {
        // CSV解析 → デバイス存在・状態チェック → リスト返却
    }
}

// app/Http/UseCases/Rental/StoreRentalWithFileUseCase.php
class StoreRentalWithFileUseCase
{
    public function __invoke(array $requestInfo, array $lists): void
    {
        DB::transaction(function () use ($requestInfo, $lists) {
            // RentalHist作成 → デバイス更新 → pivot登録
        });
    }
}
```

#### 3-3. 返却処理 UseCase の抽出

**対象:** `RentalHistsController::return()`, `RentalHistsController::storeReturnDeviceMulti()`

```php
// app/Http/UseCases/Rental/ReturnDeviceUseCase.php
class ReturnDeviceUseCase
{
    public function __invoke(string $lendId, string $deviceId, string $returnAt, array $deviceStatus): void
    {
        DB::transaction(function () use ($lendId, $deviceId, $returnAt, $deviceStatus) {
            // pivot更新 → 全返却判定 → rental_hists更新 → device更新
        });
    }
}

// app/Http/UseCases/Rental/ReturnDeviceMultiUseCase.php
class ReturnDeviceMultiUseCase
{
    public function __invoke(string $lendId): void
    {
        DB::transaction(function () use ($lendId) {
            // 全デバイスのlending_now解除 → pivot一括更新 → rental_hists更新
        });
    }
}
```

---

### Phase 4: SalesHistsController のリファクタリング（影響範囲: 中）

Phase 3 と同様のパターンで UseCase を抽出する。

#### 4-1. カート販売 UseCase の抽出

**対象:** `SalesHistsController::storeWithCart()`

```php
// app/Http/UseCases/Sale/StoreWithCartUseCase.php
class StoreWithCartUseCase
{
    public function __invoke(array $validated): SaleHist
    {
        return DB::transaction(function () use ($validated) {
            // SaleHist作成 → デバイスsale_id更新 → pivot登録
        });
    }
}
```

#### 4-2. CSV販売 UseCase の抽出

**対象:** `SalesHistsController::upload()` + `SalesHistsController::store()`

```php
// app/Http/UseCases/Sale/UploadSaleFileUseCase.php
// app/Http/UseCases/Sale/StoreWithFileUseCase.php
```

#### 4-3. 履歴編集 UseCase の抽出

**対象:** `SalesHistsController::editSaleHistory()`

```php
// app/Http/UseCases/Sale/EditSaleHistoryUseCase.php
```

---

### Phase 5: テストの拡充（影響範囲: 小）

UseCase単位でユニットテストを作成する。UseCase はControllerから分離されているため、テストが格段に書きやすくなる。

#### 5-1. テストディレクトリ構成

```
tests/
├── Unit/
│   ├── UseCases/
│   │   ├── Device/
│   │   │   ├── StoreDeviceUseCaseTest.php
│   │   │   ├── UpdateDeviceUseCaseTest.php
│   │   │   ├── SearchDeviceUseCaseTest.php
│   │   │   └── ...
│   │   ├── Rental/
│   │   │   ├── StoreRentalWithCartUseCaseTest.php
│   │   │   ├── ReturnDeviceUseCaseTest.php
│   │   │   └── ...
│   │   └── Sale/
│   │       ├── StoreWithCartUseCaseTest.php
│   │       └── ...
│   └── ... (既存テスト)
└── Feature/
    ├── DeviceRegistrationTest.php
    ├── RentalProcessTest.php
    └── SaleProcessTest.php
```

#### 5-2. テスト方針

- UseCase のユニットテストでは **DB操作は実際に行う**（`RefreshDatabase` trait使用）
- Eloquent Model をモックしない（なんちゃってクリーンアーキテクチャの方針に従う）
- 外部サービス（ImageProcessor, SendGrid等）のみモック対象
- 正常系・異常系の両方をカバー

---

## 4. リファクタリング前後の比較

### DevicesController::storeDevice() の例

#### Before（現状: 60行超）

```php
public function storeDevice(StoreDeviceRequest $request)
{
    try {
        $safe = $request->safe()->toArray();
        DB::beginTransaction();
        Device::create([...]);

        if (array_key_exists('device_image', $safe)) {
            $imgPro = new ImageProcessor();
            $img_info = $imgPro->process($request->file('device_image'));
            // ... 画像処理ロジック (20行)
        }

        DB::commit();
        return redirect()->back()->with('success_message', ...);
    } catch (\Exception $err) {
        DB::rollBack();
        Log::channel('error')->error(...);
        return redirect()->back()->with('error_message', ...);
    }
}
```

#### After（目標: 10行程度）

```php
public function storeDevice(StoreDeviceRequest $request, StoreDeviceUseCase $useCase)
{
    try {
        $useCase($request->safe()->toArray(), $request->file('device_image'));

        return redirect()->back()
            ->with('success_message', __('messages.registration_completed'));
    } catch (\Exception $err) {
        Log::channel('error')->error(__('messages.device_registration_failed'), [
            'error_message' => $err->getMessage(),
        ]);
        return redirect()->back()
            ->with('error_message', __('messages.registration_failed'));
    }
}
```

**ポイント:**
- Controller はリクエストの受け取り → UseCase呼び出し → レスポンス返却のみ
- ビジネスロジック（DB操作、画像処理）はすべて UseCase に移動
- UseCase は DI (Dependency Injection) でControllerメソッドに注入

---

## 5. 実装ルール

### 5-1. UseCase の命名規則

| パターン | 命名 | 例 |
|---------|------|-----|
| データ取得 | `Get{Entity}{Detail}UseCase` | `GetDeviceListUseCase` |
| データ作成 | `Store{Entity}UseCase` | `StoreDeviceUseCase` |
| データ更新 | `Update{Entity}UseCase` | `UpdateDeviceUseCase` |
| データ検索 | `Search{Entity}UseCase` | `SearchDeviceUseCase` |
| ファイルアップロード | `Upload{Entity}FileUseCase` | `UploadRentalFileUseCase` |
| 特殊処理 | `{動詞}{Entity}UseCase` | `ReturnDeviceUseCase` |

### 5-2. UseCase の実装規約

```php
namespace App\Http\UseCases\{Domain};

class {Name}UseCase
{
    // 依存がある場合のみコンストラクタインジェクション
    public function __construct(
        private ImageProcessor $imageProcessor,
    ) {}

    // 必ず __invoke() のみ。他のpublicメソッドは持たない。
    public function __invoke(/* 引数 */): /* 戻り値 */
    {
        // ビジネスロジック
    }

    // privateメソッドは必要に応じて切り出し可
    private function helperMethod(): void
    {
        //
    }
}
```

### 5-3. やらないこと（スコープ外）

- **Repositoryパターンの導入** - Eloquent Modelを直接使う
- **DTOの導入** - `$request->safe()->toArray()` で配列を渡す
- **ドメインモデル / Entity の作成** - Eloquent Model をそのまま使う
- **海中レベルUseCaseの作成** - 現時点では不要（複雑化してから検討）
- **既存のルーティング変更** - ルートは変更せず、Controller内部のみ変更
- **既存のビュー変更** - ビューへ渡すデータ構造は維持

---

## 6. 実装スケジュール

| Phase | 内容 | 作成ファイル数 | 既存TODO関連 |
|-------|------|--------------|-------------|
| **Phase 1** | 基盤整備（重複統合・FormRequest整備） | UseCase: 1, FormRequest: 7 | - |
| **Phase 2** | DevicesController リファクタリング | UseCase: 7 | DeviceService作成, 重複メソッド統合 |
| **Phase 3** | RentalHistsController リファクタリング | UseCase: 8 | RentalService作成 |
| **Phase 4** | SalesHistsController リファクタリング | UseCase: 6 | - |
| **Phase 5** | テスト拡充 | テスト: 10+ | DeviceRegistrationTest, RentalProcessTest |

---

## 7. 既存TODOとの整合性

`docs/TODO.md` に記載されている改善タスクとの対応：

| 既存TODO | 本計画での対応 |
|---------|-------------|
| N+1問題修正 (`DevicesController::deviceIndividual`) | Phase 2-5 の `GetDeviceDetailUseCase` で対応 |
| DeviceServiceクラス作成 | Phase 2 で UseCase として分離（Service ではなく UseCase パターンを採用） |
| RentalServiceクラス作成 | Phase 3 で UseCase として分離（同上） |
| 重複メソッドの統合 | Phase 1-1 で `GetPersonnelUseCase` として統合 |
| DeviceRegistrationTest作成 | Phase 5 で UseCase 単位のテスト + Feature テストを作成 |
| RentalProcessTest作成 | Phase 5 で UseCase 単位のテスト + Feature テストを作成 |
| CSVエラーメッセージの改善 | Phase 2-3 の `ConfirmDeviceMultiUseCase` 内で対応 |

> **注意:** 既存TODOでは「Serviceクラス」と記載されているが、本計画では記事に倣い「UseCase」パターンを採用する。単一責務のクラスとして1アクション = 1クラスとすることで、Serviceクラスの肥大化（Fat Service）を防ぐ。

---

## 8. リスクと対策

| リスク | 対策 |
|-------|------|
| UseCase導入によるファイル数増加 | ドメインごとにディレクトリを分け、命名規則を統一することで管理しやすくする |
| リファクタリング中のデグレ | Phase単位で段階的に実施し、各Phase完了後に手動テスト + 既存テストを実行 |
| チーム内での学習コスト | UseCase は `__invoke()` のみのシンプルな構造。既存のLaravel知識で理解できる |
| ビューへのデータ構造変更 | UseCase の戻り値は既存の変数名・構造を維持し、ビュー修正を不要にする |
