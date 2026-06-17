<?php

namespace Tests\Feature\Api;

use App\Models\Client;
use App\Models\Contacts;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * 担当者一覧／詳細 API のFeatureテスト。
 */
class ContactApiTest extends TestCase
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

    private function makeContact(Client $client, array $overrides = []): Contacts
    {
        $now = now();

        return Contacts::create(array_merge([
            'client_id'   => $client->client_id,
            'name'        => '山田太郎',
            'tel'         => '090-0000-0000',
            'email'       => 'yamada@example.com',
            'note'        => '担当',
            'created_at'  => $now,
            'modified_at' => $now,
        ], $overrides));
    }

    public function test_contacts_requires_authentication(): void
    {
        $this->getJson('/api/contacts')->assertStatus(401);
    }

    public function test_index_returns_contacts_with_company_in_data_wrapper(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $client = $this->makeClient(['company' => 'Alpha社']);
        $this->makeContact($client, ['name' => '佐藤']);
        $this->makeContact($client, ['name' => '鈴木']);

        $response = $this->getJson('/api/contacts');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => [['id', 'client_id', 'company', 'name', 'tel', 'email', 'note', 'modified_at']]])
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.company', 'Alpha社');
    }

    public function test_index_excludes_soft_deleted(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $client = $this->makeClient();
        $this->makeContact($client, ['name' => '生きてる']);
        $this->makeContact($client, ['name' => '削除済み', 'soft_deleted_at' => now()]);

        $response = $this->getJson('/api/contacts');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', '生きてる');
    }

    public function test_index_filters_by_word(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $client = $this->makeClient();
        $this->makeContact($client, ['name' => '田中一郎']);
        $this->makeContact($client, ['name' => '高橋花子']);

        $response = $this->getJson('/api/contacts?word=田中');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', '田中一郎');
    }

    public function test_show_returns_contact_with_company(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $client  = $this->makeClient(['company' => 'Alpha社']);
        $contact = $this->makeContact($client, ['name' => '山田太郎']);

        $response = $this->getJson('/api/contacts/' . $contact->id);

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['id', 'client_id', 'company', 'name', 'tel', 'email', 'note', 'modified_at']])
            ->assertJsonPath('data.name', '山田太郎')
            ->assertJsonPath('data.company', 'Alpha社');
    }

    public function test_show_returns_404_for_unknown_contact(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/contacts/999999')->assertStatus(404);
    }
}
