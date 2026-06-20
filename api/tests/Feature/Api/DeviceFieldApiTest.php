<?php

namespace Tests\Feature\Api;

use App\Models\DeviceCategory;
use App\Models\DeviceTypeField;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceFieldApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $member;
    protected DeviceCategory $category;

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

        $this->category = DeviceCategory::create([
            'code' => 'STB',
            'name' => 'STB',
            'icon' => 'fa-cube',
            'sort_order' => 1,
            'is_active' => true,
        ]);
    }

    private function makeField(string $label, string $key, int $order = 0): DeviceTypeField
    {
        return DeviceTypeField::create([
            'device_category_code' => $this->category->code,
            'field_key' => $key,
            'label' => $label,
            'field_type' => 'text',
            'options' => null,
            'is_required' => false,
            'sort_order' => $order,
        ]);
    }

    public function test_index_requires_authentication(): void
    {
        $this->getJson('/api/device-fields')->assertStatus(401);
    }

    public function test_index_forbidden_for_non_admin(): void
    {
        $this->actingAs($this->member, 'sanctum')
            ->getJson('/api/device-fields')
            ->assertStatus(403);
    }

    public function test_index_returns_fields_and_types(): void
    {
        $this->makeField('OS', 'os', 1);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/device-fields');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [['id', 'device_category_code', 'field_key', 'label', 'field_type', 'options', 'is_required', 'sort_order']],
            'field_types',
        ]);
        $this->assertSame(1, count($response->json('data')));
    }

    public function test_index_filters_by_category(): void
    {
        $other = DeviceCategory::create([
            'code' => 'CAM', 'name' => 'カメラ', 'icon' => 'fa-cube', 'sort_order' => 2, 'is_active' => true,
        ]);
        $this->makeField('OS', 'os', 1);
        DeviceTypeField::create([
            'device_category_code' => $other->code, 'field_key' => 'lens', 'label' => 'レンズ',
            'field_type' => 'text', 'options' => null, 'is_required' => false, 'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/device-fields?category=STB');

        $response->assertStatus(200);
        $this->assertSame(1, count($response->json('data')));
        $this->assertSame('STB', $response->json('data.0.device_category_code'));
    }

    public function test_store_creates_text_field_with_generated_key(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/device-fields', [
                'device_category_code' => 'STB',
                'label' => 'Screen Size',
                'field_type' => 'text',
            ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.field_key', 'screen_size');
        $this->assertDatabaseHas('device_type_fields', [
            'device_category_code' => 'STB',
            'field_key' => 'screen_size',
        ]);
    }

    public function test_store_select_field_keeps_options(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/device-fields', [
                'device_category_code' => 'STB',
                'label' => 'カラー',
                'field_type' => 'select',
                'options' => [
                    ['label' => '黒', 'value' => 'black'],
                    ['label' => '白', 'value' => 'white'],
                ],
            ]);

        $response->assertStatus(201);
        $this->assertCount(2, $response->json('data.options'));
    }

    public function test_store_validates_field_type(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/device-fields', [
                'device_category_code' => 'STB',
                'label' => 'だめ',
                'field_type' => 'invalid',
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('errors.field_type.0', 'フィールド種別の指定が不正です。');
    }

    public function test_store_forbidden_for_non_admin(): void
    {
        $this->actingAs($this->member, 'sanctum')
            ->postJson('/api/device-fields', [
                'device_category_code' => 'STB',
                'label' => 'OS',
                'field_type' => 'text',
            ])
            ->assertStatus(403);
    }

    public function test_update_changes_type_and_clears_options(): void
    {
        $field = DeviceTypeField::create([
            'device_category_code' => 'STB', 'field_key' => 'color', 'label' => 'カラー',
            'field_type' => 'select', 'options' => [['label' => '黒', 'value' => 'black']],
            'is_required' => false, 'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/device-fields/{$field->id}", [
                'label' => 'カラー名',
                'field_type' => 'text',
                'is_required' => true,
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.options', null);
        $this->assertDatabaseHas('device_type_fields', [
            'id' => $field->id,
            'label' => 'カラー名',
            'field_type' => 'text',
            'is_required' => true,
        ]);
    }

    public function test_update_returns_404_for_missing(): void
    {
        $this->actingAs($this->admin, 'sanctum')
            ->putJson('/api/device-fields/999999', ['label' => 'x', 'field_type' => 'text'])
            ->assertStatus(404);
    }

    public function test_destroy_deletes_field(): void
    {
        $field = $this->makeField('OS', 'os', 1);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/device-fields/{$field->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('device_type_fields', ['id' => $field->id]);
    }

    public function test_reorder_updates_sort_order(): void
    {
        $a = $this->makeField('A', 'a', 1);
        $b = $this->makeField('B', 'b', 2);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/device-fields/reorder', [
                'order' => [$b->id, $a->id],
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('device_type_fields', ['id' => $b->id, 'sort_order' => 1]);
        $this->assertDatabaseHas('device_type_fields', ['id' => $a->id, 'sort_order' => 2]);
    }
}
