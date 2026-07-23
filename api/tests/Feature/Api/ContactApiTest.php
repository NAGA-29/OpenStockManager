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

    public function test_store_requires_authentication(): void
    {
        $this->postJson('/api/contacts', [])->assertStatus(401);
    }

    public function test_store_creates_contact_and_returns_201(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $client = $this->makeClient();

        $response = $this->postJson('/api/contacts', [
            'client_id' => $client->client_id,
            'name'      => '新規担当者',
            'email'     => 'new@example.com',
            'tel'       => '09012345678',
            'note'      => 'メモ',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['data' => ['id', 'client_id', 'company', 'name', 'tel', 'email', 'note', 'modified_at']])
            ->assertJsonPath('data.name', '新規担当者')
            ->assertJsonPath('data.client_id', $client->client_id);

        $this->assertDatabaseHas('contacts', [
            'client_id' => $client->client_id,
            'name'      => '新規担当者',
            'email'     => 'new@example.com',
        ]);
    }

    public function test_store_validates_client_id_required(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson('/api/contacts', [
            'name'  => '新規担当者',
            'email' => 'new@example.com',
            'tel'   => '09012345678',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.client_id.0', 'クライアントを選択してください。');
    }

    public function test_store_validates_client_id_exists(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson('/api/contacts', [
            'client_id' => 'non-existent-id',
            'name'      => '新規担当者',
            'email'     => 'new@example.com',
            'tel'       => '09012345678',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.client_id.0', '指定されたクライアントが見つかりません。');
    }

    public function test_store_validates_email_format(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $client = $this->makeClient();

        $response = $this->postJson('/api/contacts', [
            'client_id' => $client->client_id,
            'name'      => '新規担当者',
            'email'     => 'invalid-email',
            'tel'       => '09012345678',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.email.0', '有効なメールアドレスを入力してください。');
    }

    public function test_store_validates_tel_digits(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $client = $this->makeClient();

        $response = $this->postJson('/api/contacts', [
            'client_id' => $client->client_id,
            'name'      => '新規担当者',
            'email'     => 'new@example.com',
            'tel'       => '123',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.tel.0', '電話番号は8〜11桁で入力してください。');
    }
}
