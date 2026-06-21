<?php

namespace Tests\Feature\Api;

use App\Models\Client;
use App\Models\Contacts;
use App\Models\RentalHist;
use App\Models\SaleHist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HistoryApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Client $client;
    protected Contacts $contact;

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
    }

    private function makeRental(string $lendId): void
    {
        RentalHist::create([
            'lend_id' => $lendId,
            'client' => $this->client->client_id,
            'contact' => $this->contact->id,
            'staff' => $this->user->id,
            'all_returned' => 0,
            'checkout_at' => now(),
            'schedule_return_at' => now()->addDays(7),
            'note' => 'rental note',
        ]);
    }

    private function makeSale(string $saleId): void
    {
        SaleHist::create([
            'sale_id' => $saleId,
            'client' => $this->client->client_id,
            'contact' => $this->contact->id,
            'staff' => $this->user->id,
            'sale_date_at' => now(),
            'note' => 'sale note',
        ]);
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/history');
        $response->assertStatus(401);
    }

    public function test_index_returns_merged_history(): void
    {
        $this->makeRental('RENT-001');
        $this->makeSale('SALE-001');

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/history');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [['id', 'type', 'company', 'contact', 'date', 'status', 'note']],
            'meta' => ['current_page', 'last_page', 'per_page', 'total'],
        ]);
        $this->assertSame(2, $response->json('meta.total'));
    }

    public function test_index_filters_by_rental_type(): void
    {
        $this->makeRental('RENT-001');
        $this->makeSale('SALE-001');

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/history?type=rental');

        $response->assertStatus(200);
        $this->assertSame(1, $response->json('meta.total'));
        $this->assertSame('rental', $response->json('data.0.type'));
    }

    public function test_index_filters_by_sale_type(): void
    {
        $this->makeRental('RENT-001');
        $this->makeSale('SALE-001');

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/history?type=sale');

        $response->assertStatus(200);
        $this->assertSame(1, $response->json('meta.total'));
        $this->assertSame('sale', $response->json('data.0.type'));
    }

    public function test_index_searches_by_keyword(): void
    {
        $this->makeRental('RENT-001');
        $this->makeSale('SALE-001');

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/history?word=' . urlencode('テスト商事'));

        $response->assertStatus(200);
        $this->assertSame(2, $response->json('meta.total'));
    }

    public function test_index_paginates_results(): void
    {
        for ($i = 1; $i <= 8; $i++) {
            $this->makeRental('RENT-' . str_pad((string) $i, 3, '0', STR_PAD_LEFT));
        }
        for ($i = 1; $i <= 8; $i++) {
            $this->makeSale('SALE-' . str_pad((string) $i, 3, '0', STR_PAD_LEFT));
        }

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/history?page=2');

        $response->assertStatus(200);
        $this->assertSame(16, $response->json('meta.total'));
        $this->assertSame(2, $response->json('meta.last_page'));
        $this->assertCount(6, $response->json('data'));
    }
}
