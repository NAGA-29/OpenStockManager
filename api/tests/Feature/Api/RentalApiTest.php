<?php

namespace Tests\Feature\Api;

use App\Models\Client;
use App\Models\Contacts;
use App\Models\Device;
use App\Models\RentalHist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RentalApiTest extends TestCase
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
        $response = $this->getJson('/api/rental');
        $response->assertStatus(401);
    }

    public function test_index_returns_rental_list(): void
    {
        $rental = RentalHist::create([
            'lend_id' => 'RENT-001',
            'client' => $this->client->client_id,
            'contact' => $this->contact->id,
            'staff' => $this->user->id,
            'checkout_at' => now(),
            'schedule_return_at' => now()->addDays(7),
        ]);
        $rental->devices()->attach($this->device->device_id, ['checkout_at' => now()]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/rental');

        $response->assertStatus(200);
        $response->assertJsonStructure(['data', 'meta']);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_store_requires_authentication(): void
    {
        $response = $this->postJson('/api/rental/store', [
            'device_ids' => ['TEST-001'],
            'client_id' => $this->client->client_id,
            'contact_id' => $this->contact->id,
            'checkout_at' => now()->format('Y-m-d'),
            'schedule_return_at' => now()->addDays(7)->format('Y-m-d'),
        ]);
        $response->assertStatus(401);
    }

    public function test_store_creates_rental_with_valid_data(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/rental/store', [
                'device_ids' => ['TEST-001'],
                'client_id' => $this->client->client_id,
                'contact_id' => $this->contact->id,
                'checkout_at' => now()->format('Y-m-d'),
                'schedule_return_at' => now()->addDays(7)->format('Y-m-d'),
                'note' => 'Test rental',
            ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['data', 'message']);
        $this->assertDatabaseHas('rental_hists', [
            'client' => $this->client->client_id,
            'contact' => $this->contact->id,
        ]);
    }

    public function test_store_validates_device_ids_required(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/rental/store', [
                'device_ids' => [],
                'client_id' => $this->client->client_id,
                'contact_id' => $this->contact->id,
                'checkout_at' => now()->format('Y-m-d'),
                'schedule_return_at' => now()->addDays(7)->format('Y-m-d'),
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('errors.device_ids.0', '端末を選択してください。');
    }

    public function test_store_validates_client_id_exists(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/rental/store', [
                'device_ids' => ['TEST-001'],
                'client_id' => 'INVALID-ID',
                'contact_id' => $this->contact->id,
                'checkout_at' => now()->format('Y-m-d'),
                'schedule_return_at' => now()->addDays(7)->format('Y-m-d'),
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('errors.client_id.0', '指定されたクライアントが見つかりません。');
    }

    public function test_history_returns_rental_history(): void
    {
        $rental = RentalHist::create([
            'lend_id' => 'RENT-001',
            'client' => $this->client->client_id,
            'contact' => $this->contact->id,
            'staff' => $this->user->id,
            'checkout_at' => now(),
            'schedule_return_at' => now()->addDays(7),
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/rental/history');

        $response->assertStatus(200);
        $response->assertJsonStructure(['data', 'meta']);
    }

    public function test_history_detail_returns_single_rental(): void
    {
        $rental = RentalHist::create([
            'lend_id' => 'RENT-001',
            'client' => $this->client->client_id,
            'contact' => $this->contact->id,
            'staff' => $this->user->id,
            'checkout_at' => now(),
            'schedule_return_at' => now()->addDays(7),
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/rental/history/RENT-001');

        $response->assertStatus(200);
        $response->assertJsonPath('data.lend_id', 'RENT-001');
    }

    public function test_history_detail_returns_404_for_missing_rental(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/rental/history/INVALID-ID');

        $response->assertStatus(404);
    }

    public function test_upload_multi_parses_csv(): void
    {
        $csvContent = "device_id,device_type\nTEST-001,STB\n";
        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent(
            'rentals.csv',
            $csvContent
        );

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/rental/multi/upload', [
                'rental_file' => $file,
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['data', 'count']);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_store_multi_creates_bulk_rentals(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/rental/multi/store', [
                'client_id' => $this->client->client_id,
                'contact_id' => $this->contact->id,
                'checkout_at' => now()->format('Y-m-d'),
                'schedule_return_at' => now()->addDays(7)->format('Y-m-d'),
                'rentals' => [
                    ['device_id' => 'TEST-001'],
                ],
                'note' => 'Bulk rental',
            ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['data', 'count', 'message']);
    }

    public function test_return_device_marks_as_returned(): void
    {
        // Update device to mark as currently renting
        $this->device->update(['lending_now' => 'RENT-001']);

        $rental = RentalHist::create([
            'lend_id' => 'RENT-001',
            'client' => $this->client->client_id,
            'contact' => $this->contact->id,
            'staff' => $this->user->id,
            'all_returned' => 0,
            'checkout_at' => now(),
            'schedule_return_at' => now()->addDays(7),
        ]);
        $rental->devices()->attach($this->device->device_id, ['checkout_at' => now()]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/rental/multi/return/RENT-001', [
                'device_id' => 'TEST-001',
                'return_at' => now()->format('Y-m-d'),
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('devices', [
            'device_id' => 'TEST-001',
            'lending_now' => '',
        ]);
    }

    public function test_store_rejects_already_lent_device(): void
    {
        $this->device->update(['lending_now' => 'RENT-EXISTING']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/rental/store', [
                'device_ids' => ['TEST-001'],
                'client_id' => $this->client->client_id,
                'contact_id' => $this->contact->id,
                'checkout_at' => now()->format('Y-m-d'),
                'schedule_return_at' => now()->addDays(7)->format('Y-m-d'),
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('device_ids');
    }

    public function test_store_rejects_sold_device(): void
    {
        $this->device->update(['sale_id' => 'SALE-EXISTING']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/rental/store', [
                'device_ids' => ['TEST-001'],
                'client_id' => $this->client->client_id,
                'contact_id' => $this->contact->id,
                'checkout_at' => now()->format('Y-m-d'),
                'schedule_return_at' => now()->addDays(7)->format('Y-m-d'),
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('device_ids');
    }

    public function test_store_multi_rejects_already_lent_device(): void
    {
        $this->device->update(['lending_now' => 'RENT-EXISTING']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/rental/multi/store', [
                'client_id' => $this->client->client_id,
                'contact_id' => $this->contact->id,
                'checkout_at' => now()->format('Y-m-d'),
                'schedule_return_at' => now()->addDays(7)->format('Y-m-d'),
                'rentals' => [
                    ['device_id' => 'TEST-001'],
                ],
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('rentals');
    }
}
