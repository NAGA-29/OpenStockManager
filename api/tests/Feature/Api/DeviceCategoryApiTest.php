<?php

namespace Tests\Feature\Api;

use App\Models\Device;
use App\Models\DeviceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceCategoryApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $member;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ADMIN,
        ]);

        $this->member = User::create([
            'name' => 'Member',
            'email' => 'member@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_USER,
        ]);
    }

    private function makeCategory(string $code, int $order = 0): DeviceCategory
    {
        return DeviceCategory::create([
            'code' => $code,
            'name' => $code . ' 機材',
            'icon' => 'fa-cube',
            'sort_order' => $order,
            'is_active' => true,
        ]);
    }

    public function test_index_requires_authentication(): void
    {
        $this->getJson('/api/device-categories')->assertStatus(401);
    }

    public function test_index_forbidden_for_non_admin(): void
    {
        $this->actingAs($this->member, 'sanctum')
            ->getJson('/api/device-categories')
            ->assertStatus(403);
    }

    public function test_index_returns_categories_with_device_count(): void
    {
        $this->makeCategory('STB', 1);
        Device::create([
            'device_id' => 'STB-001',
            'device_type' => 'STB',
            'device_name' => '端末',
            'device_serial' => 'SN-1',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/device-categories');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [['id', 'code', 'name', 'icon', 'sort_order', 'is_active', 'device_count']],
        ]);
        $response->assertJsonPath('data.0.device_count', 1);
    }

    public function test_store_creates_category(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/device-categories', [
                'code' => 'CAM',
                'name' => 'カメラ',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('device_categories', ['code' => 'CAM', 'name' => 'カメラ']);
    }

    public function test_store_validates_code_format(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/device-categories', [
                'code' => 'lower-case',
                'name' => 'だめ',
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('errors.code.0', 'コードは半角英大文字・数字・アンダースコアのみ使用できます。');
    }

    public function test_store_validates_unique_code(): void
    {
        $this->makeCategory('STB');

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/device-categories', [
                'code' => 'STB',
                'name' => '重複',
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('errors.code.0', 'このコードは既に使用されています。');
    }

    public function test_store_forbidden_for_non_admin(): void
    {
        $this->actingAs($this->member, 'sanctum')
            ->postJson('/api/device-categories', ['code' => 'CAM', 'name' => 'カメラ'])
            ->assertStatus(403);
    }

    public function test_update_renames_code_and_migrates_devices(): void
    {
        $category = $this->makeCategory('OLD');
        Device::create([
            'device_id' => 'OLD-001',
            'device_type' => 'OLD',
            'device_name' => '端末',
            'device_serial' => 'SN-1',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/device-categories/{$category->id}", [
                'code' => 'NEW',
                'name' => '新カテゴリ',
                'is_active' => true,
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('devices', ['device_id' => 'OLD-001', 'device_type' => 'NEW']);
        $this->assertDatabaseHas('device_categories', ['id' => $category->id, 'code' => 'NEW']);
    }

    public function test_update_returns_404_for_missing(): void
    {
        $this->actingAs($this->admin, 'sanctum')
            ->putJson('/api/device-categories/999999', ['code' => 'X', 'name' => 'x'])
            ->assertStatus(404);
    }

    public function test_destroy_blocks_when_devices_exist(): void
    {
        $category = $this->makeCategory('STB');
        Device::create([
            'device_id' => 'STB-001',
            'device_type' => 'STB',
            'device_name' => '端末',
            'device_serial' => 'SN-1',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/device-categories/{$category->id}");

        $response->assertStatus(422);
        $this->assertDatabaseHas('device_categories', ['id' => $category->id]);
    }

    public function test_destroy_deletes_empty_category(): void
    {
        $category = $this->makeCategory('EMPTY');

        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/device-categories/{$category->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('device_categories', ['id' => $category->id]);
    }

    public function test_reorder_updates_sort_order(): void
    {
        $a = $this->makeCategory('AAA', 1);
        $b = $this->makeCategory('BBB', 2);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/device-categories/reorder', [
                'order' => [$b->id, $a->id],
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('device_categories', ['id' => $b->id, 'sort_order' => 1]);
        $this->assertDatabaseHas('device_categories', ['id' => $a->id, 'sort_order' => 2]);
    }
}
