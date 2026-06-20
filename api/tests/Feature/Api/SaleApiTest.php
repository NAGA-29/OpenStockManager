<?php

namespace Tests\Feature\Api;

use App\Models\Client;
use App\Models\Contacts;
use App\Models\Device;
use App\Models\SaleHist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Client $client;
    protected Contacts $contact;
    protected Device $device;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->client = Client::create([
            'client_id' => 'C-' . uniqid(),
            'company' => 'テスト商事',
            'url' => 'https://example.com',
            'tel' => '03-0000-0000',
            'street_address' => '東京都',
        ]);

        $this->contact = Contacts::create([
            'client_id' => $this->client->client_id,
            'name' => '山田太郎',
            'tel' => '090-0000-0000',
            'email' => 'yamada@example.com',
        ]);

        $this->device = Device::create([
            'device_id' => 'TEST-001',
            'device_type' => 'STB',
            'device_name' => 'テスト端末',
            'device_serial' => 'SN-001',
        ]);
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/sale');
        $response->assertStatus(401);
    }

    public function test_index_returns_sale_list(): void
    {
        $sale = SaleHist::create([
            'sale_id' => 'SALE-001',
            'client' => $this->client->client_id,
            'contact' => $this->contact->id,
            'staff' => $this->user->id,
            'sale_date_at' => now(),
        ]);
        $sale->devices()->attach($this->device->device_id, ['sale_date_at' => now()]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/sale');

        $response->assertStatus(200);
        $response->assertJsonStructure(['data', 'meta']);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_store_requires_authentication(): void
    {
        $response = $this->postJson('/api/sale/store', [
            'device_ids' => ['TEST-001'],
            'client_id' => $this->client->client_id,
            'contact_id' => $this->contact->id,
            'sale_date_at' => now()->format('Y-m-d'),
        ]);
        $response->assertStatus(401);
    }

    public function test_store_creates_sale_with_valid_data(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/sale/store', [
                'device_ids' => ['TEST-001'],
                'client_id' => $this->client->client_id,
                'contact_id' => $this->contact->id,
                'sale_date_at' => now()->format('Y-m-d'),
                'note' => 'Test sale',
            ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['data', 'message']);
        $this->assertDatabaseHas('sale_hists', [
            'client' => $this->client->client_id,
            'contact' => $this->contact->id,
        ]);
    }

    public function test_store_updates_device_sale_id(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/sale/store', [
                'device_ids' => ['TEST-001'],
                'client_id' => $this->client->client_id,
                'contact_id' => $this->contact->id,
                'sale_date_at' => now()->format('Y-m-d'),
            ]);

        $response->assertStatus(201);
        $this->assertNotEmpty(Device::find('TEST-001')->sale_id);
    }

    public function test_store_validates_device_ids_required(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/sale/store', [
                'device_ids' => [],
                'client_id' => $this->client->client_id,
                'contact_id' => $this->contact->id,
                'sale_date_at' => now()->format('Y-m-d'),
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('errors.device_ids.0', '端末を選択してください。');
    }

    public function test_store_validates_client_id_exists(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/sale/store', [
                'device_ids' => ['TEST-001'],
                'client_id' => 'INVALID-ID',
                'contact_id' => $this->contact->id,
                'sale_date_at' => now()->format('Y-m-d'),
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('errors.client_id.0', '指定されたクライアントが見つかりません。');
    }

    public function test_history_returns_sale_history(): void
    {
        SaleHist::create([
            'sale_id' => 'SALE-001',
            'client' => $this->client->client_id,
            'contact' => $this->contact->id,
            'staff' => $this->user->id,
            'sale_date_at' => now(),
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/sale/history');

        $response->assertStatus(200);
        $response->assertJsonStructure(['data', 'meta']);
    }

    public function test_history_detail_returns_single_sale(): void
    {
        SaleHist::create([
            'sale_id' => 'SALE-001',
            'client' => $this->client->client_id,
            'contact' => $this->contact->id,
            'staff' => $this->user->id,
            'sale_date_at' => now(),
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/sale/history/SALE-001');

        $response->assertStatus(200);
        $response->assertJsonPath('data.sale_id', 'SALE-001');
    }

    public function test_history_detail_returns_404_for_missing_sale(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/sale/history/INVALID-ID');

        $response->assertStatus(404);
    }

    public function test_upload_multi_parses_csv(): void
    {
        $csvContent = "device_id,device_type\nTEST-001,STB\n";
        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent(
            'sales.csv',
            $csvContent
        );

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/sale/multi/upload', [
                'sale_file' => $file,
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['data', 'count']);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_store_multi_creates_bulk_sales(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/sale/multi/store', [
                'client_id' => $this->client->client_id,
                'contact_id' => $this->contact->id,
                'sale_date_at' => now()->format('Y-m-d'),
                'sales' => [
                    ['device_id' => 'TEST-001'],
                ],
                'note' => 'Bulk sale',
            ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['data', 'count', 'message']);
    }

    public function test_store_rejects_already_sold_device(): void
    {
        $this->device->update(['sale_id' => 'SALE-EXISTING']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/sale/store', [
                'device_ids' => ['TEST-001'],
                'client_id' => $this->client->client_id,
                'contact_id' => $this->contact->id,
                'sale_date_at' => now()->format('Y-m-d'),
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('device_ids');
    }

    public function test_store_rejects_lent_device(): void
    {
        $this->device->update(['lending_now' => 'RENT-001']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/sale/store', [
                'device_ids' => ['TEST-001'],
                'client_id' => $this->client->client_id,
                'contact_id' => $this->contact->id,
                'sale_date_at' => now()->format('Y-m-d'),
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('device_ids');
    }

    public function test_store_rejects_not_for_sale_device(): void
    {
        $this->device->update(['not_for_sale' => true]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/sale/store', [
                'device_ids' => ['TEST-001'],
                'client_id' => $this->client->client_id,
                'contact_id' => $this->contact->id,
                'sale_date_at' => now()->format('Y-m-d'),
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('device_ids');
    }

    public function test_store_multi_rejects_already_sold_device(): void
    {
        $this->device->update(['sale_id' => 'SALE-EXISTING']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/sale/multi/store', [
                'client_id' => $this->client->client_id,
                'contact_id' => $this->contact->id,
                'sale_date_at' => now()->format('Y-m-d'),
                'sales' => [
                    ['device_id' => 'TEST-001'],
                ],
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('sales');
    }
}
