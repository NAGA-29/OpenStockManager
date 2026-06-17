<?php

namespace Tests\Feature\Api;

use App\Models\Client;
use App\Models\Contacts;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * クライアント一覧／詳細 API のFeatureテスト。
 */
class ClientApiTest extends TestCase
{
    use RefreshDatabase;

    private function makeClient(array $overrides = []): Client
    {
        $now = now();

        return Client::create(array_merge([
            'client_id'      => 'C-' . uniqid(),
            'company'        => 'テスト商事',
            'url'            => 'https://example.com',
            'tel'            => '03-0000-0000',
            'street_address' => '東京都',
            'note'           => 'メモ',
            'created_at'     => $now,
            'modified_at'    => $now,
        ], $overrides));
    }

    public function test_clients_requires_authentication(): void
    {
        $this->getJson('/api/clients')->assertStatus(401);
    }

    public function test_index_returns_clients_in_data_wrapper(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->makeClient(['client_id' => 'C-1', 'company' => 'Alpha社']);
        $this->makeClient(['client_id' => 'C-2', 'company' => 'Beta社']);

        $response = $this->getJson('/api/clients');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => [['client_id', 'company', 'url', 'street_address', 'note', 'modified_at']]])
            ->assertJsonCount(2, 'data');
    }

    public function test_index_filters_by_word(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->makeClient(['client_id' => 'C-1', 'company' => 'Alpha社']);
        $this->makeClient(['client_id' => 'C-2', 'company' => 'Beta社']);

        $response = $this->getJson('/api/clients?word=Alpha');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.company', 'Alpha社');
    }

    public function test_show_returns_client_with_contacts(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $client = $this->makeClient(['client_id' => 'C-1', 'company' => 'Alpha社']);
        Contacts::create([
            'client_id'   => $client->client_id,
            'name'        => '山田太郎',
            'tel'         => '090-0000-0000',
            'email'       => 'yamada@example.com',
            'note'        => '担当',
            'created_at'  => now(),
            'modified_at' => now(),
        ]);

        $response = $this->getJson('/api/clients/C-1');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['client_id', 'company', 'contacts' => [['id', 'name', 'tel', 'email', 'note', 'modified_at']]]])
            ->assertJsonPath('data.company', 'Alpha社')
            ->assertJsonPath('data.contacts.0.name', '山田太郎');
    }

    public function test_show_returns_404_for_unknown_client(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/clients/NO_SUCH')->assertStatus(404);
    }
}
