<?php

namespace Tests\Feature\Api;

use App\Models\Device;
use App\Models\DeviceCategory;
use App\Models\DeviceTypeField;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * 個別管理（カテゴリ別一覧）／端末詳細 API のFeatureテスト。
 */
class DeviceApiTest extends TestCase
{
    use RefreshDatabase;

    private function makeDevice(array $overrides = []): Device
    {
        $now = now();

        return Device::create(array_merge([
            'device_id'     => 'STB_TEST_000001',
            'device_type'   => 'STB',
            'device_name'   => 'TestDevice',
            'device_serial' => 'SN-' . uniqid(),
            'defective'     => false,
            'not_for_sale'  => false,
            'lending_now'   => '',
            'sale_id'       => '',
            'created_at'    => $now,
            'modified_at'   => $now,
        ], $overrides));
    }

    public function test_by_category_returns_tabs_counts_and_rows(): void
    {
        Sanctum::actingAs(User::factory()->create());

        DeviceCategory::create(['code' => 'STB', 'name' => 'STB機器', 'icon' => 'fa-tv', 'sort_order' => 1, 'is_active' => true]);
        DeviceCategory::create(['code' => 'CAM', 'name' => 'カメラ', 'icon' => 'fa-camera', 'sort_order' => 2, 'is_active' => true]);

        // conditions テーブルはマイグレーションで id=1(新品) 等が投入済み。
        $this->makeDevice(['device_id' => 'STB_TEST_000001', 'condition_id' => 1]);
        $this->makeDevice(['device_id' => 'STB_TEST_000002', 'defective' => true]);

        $response = $this->getJson('/api/devices/category/STB');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'categories' => [['code', 'name', 'icon']],
                'current'    => ['code', 'name', 'icon'],
                'counts'     => ['all', 'lending', 'defective'],
                'category',
                'data'       => [['device_id', 'condition', 'has_images', 'note', 'sale_id']],
            ])
            ->assertJsonPath('current.code', 'STB')
            ->assertJsonPath('counts.all', 2)
            ->assertJsonPath('counts.defective', 1)
            ->assertJsonCount(2, 'categories')
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.1.condition', '新品');
    }

    public function test_by_category_returns_404_for_unknown_category(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/devices/category/NOPE')->assertStatus(404);
    }

    public function test_show_returns_resolved_custom_fields_and_history_keys(): void
    {
        Sanctum::actingAs(User::factory()->create());

        DeviceCategory::create(['code' => 'STB', 'name' => 'STB機器', 'icon' => 'fa-tv', 'sort_order' => 1, 'is_active' => true]);
        DeviceTypeField::create([
            'device_category_code' => 'STB',
            'field_key'            => 'color',
            'label'                => '色',
            'field_type'           => 'select',
            'options'              => [['value' => 'r', 'label' => '赤']],
            'is_required'          => false,
            'sort_order'           => 1,
        ]);

        $this->makeDevice([
            'condition_id'  => 1,
            'custom_fields' => ['color' => 'r'],
            'note'          => 'テストノート',
        ]);

        $response = $this->getJson('/api/devices/STB_TEST_000001');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'device_id', 'device_name', 'condition', 'note', 'option',
                    'using_user_id', 'first_work_date_at', 'purchase_date_at', 'modified_at',
                    'custom_fields' => [['key', 'label', 'type', 'value', 'display']],
                    'images', 'rental_hists', 'sale_hists',
                ],
            ])
            ->assertJsonPath('data.condition', '新品')
            ->assertJsonPath('data.custom_fields.0.label', '色')
            ->assertJsonPath('data.custom_fields.0.display', '赤');
    }

    public function test_show_returns_404_for_unknown_device(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/devices/NO_SUCH_DEVICE')->assertStatus(404);
    }

    public function test_form_options_returns_categories_with_fields_and_conditions(): void
    {
        Sanctum::actingAs(User::factory()->create());

        DeviceCategory::create(['code' => 'STB', 'name' => 'STB機器', 'icon' => 'fa-tv', 'sort_order' => 1, 'is_active' => true]);
        DeviceCategory::create(['code' => 'OLD', 'name' => '旧機器', 'icon' => 'fa-box', 'sort_order' => 2, 'is_active' => false]);
        DeviceTypeField::create([
            'device_category_code' => 'STB',
            'field_key'            => 'color',
            'label'                => '色',
            'field_type'           => 'text',
            'is_required'          => true,
            'sort_order'           => 1,
        ]);

        $response = $this->getJson('/api/devices/form-options');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'categories' => [['code', 'name', 'fields' => [['field_key', 'label', 'field_type', 'is_required', 'options']]]],
                'conditions' => [['id', 'label']],
            ])
            // is_active=false は除外される。
            ->assertJsonCount(1, 'categories')
            ->assertJsonPath('categories.0.code', 'STB')
            ->assertJsonPath('categories.0.fields.0.field_key', 'color')
            // conditions テーブルはマイグレーションで 4 件投入済み。
            ->assertJsonCount(4, 'conditions');
    }

    public function test_store_creates_device_with_generated_id(): void
    {
        Sanctum::actingAs(User::factory()->create());

        DeviceCategory::create(['code' => 'STB', 'name' => 'STB機器', 'icon' => 'fa-tv', 'sort_order' => 1, 'is_active' => true]);

        $response = $this->postJson('/api/devices', [
            'device_type'   => 'STB',
            'device_name'   => 'BOX',
            'device_serial' => 'SERIAL-001',
            'condition'     => 1,
            'defective'     => true,
            'note'          => 'メモ',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.device_id', 'STB_BOX_000001');

        $this->assertDatabaseHas('devices', [
            'device_serial' => 'SERIAL-001',
            'condition_id'  => 1,
            'defective'     => 1,
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson('/api/devices', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['device_type', 'device_name', 'device_serial', 'condition']);
    }

    public function test_store_rejects_duplicate_serial(): void
    {
        Sanctum::actingAs(User::factory()->create());

        DeviceCategory::create(['code' => 'STB', 'name' => 'STB機器', 'icon' => 'fa-tv', 'sort_order' => 1, 'is_active' => true]);
        $this->makeDevice(['device_id' => 'STB_X_000001', 'device_serial' => 'DUP-001']);

        $response = $this->postJson('/api/devices', [
            'device_type'   => 'STB',
            'device_name'   => 'Y',
            'device_serial' => 'DUP-001',
            'condition'     => 1,
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('device_serial');
    }

    public function test_search_requires_authentication(): void
    {
        $this->getJson('/api/devices/search?word=STB')->assertStatus(401);
    }

    public function test_search_requires_word(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/devices/search')
            ->assertStatus(422)
            ->assertJsonValidationErrors('word');
    }

    public function test_search_matches_device_id_serial_and_note(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->makeDevice(['device_id' => 'STB_ALPHA_000001', 'device_serial' => 'SN-AAA']);
        $this->makeDevice(['device_id' => 'CAM_BETA_000001', 'device_serial' => 'SN-BBB', 'note' => 'ALPHA メモ']);
        $this->makeDevice(['device_id' => 'CAM_GAMMA_000001', 'device_serial' => 'SN-CCC']);

        // device_id と note の両方に「ALPHA」が含まれる 2 件がヒット
        $response = $this->getJson('/api/devices/search?word=ALPHA');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [['device_id', 'device_serial', 'condition', 'has_images']],
                'meta' => ['current_page', 'last_page', 'per_page', 'total', 'keywords'],
            ])
            ->assertJsonPath('meta.total', 2);
    }

    public function test_search_filters_by_hidden_type(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->makeDevice(['device_id' => 'STB_ONE_000001', 'device_type' => 'STB', 'device_serial' => 'SN-KEY1']);
        $this->makeDevice(['device_id' => 'CAM_TWO_000001', 'device_type' => 'CAM', 'device_serial' => 'SN-KEY2']);

        // hiddenType=STB で device_type を絶対条件にすると STB 1 件のみ
        $response = $this->getJson('/api/devices/search?word=SN&hiddenType=STB');

        $response->assertStatus(200)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.device_id', 'STB_ONE_000001');
    }

    public function test_search_excludes_soft_deleted(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->makeDevice(['device_id' => 'STB_LIVE_000001', 'device_serial' => 'SN-LIVE']);
        $this->makeDevice(['device_id' => 'STB_DEAD_000001', 'device_serial' => 'SN-DEAD', 'soft_deleted_at' => now()]);

        $response = $this->getJson('/api/devices/search?word=SN');

        $response->assertStatus(200)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.device_id', 'STB_LIVE_000001');
    }
}
