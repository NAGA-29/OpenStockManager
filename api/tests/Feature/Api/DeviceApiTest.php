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
}
