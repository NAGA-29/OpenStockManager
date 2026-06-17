<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientApiRequest;
use App\Models\Client;
use App\Models\Contacts;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

class ClientController extends Controller
{
    /**
     * 登録クライアント企業の一覧を返す。
     *
     * 旧 `client/index.blade.php` 相当。`word` クエリで会社名の部分一致検索が可能。
     */
    public function index(Request $request): JsonResponse
    {
        $word  = $request->query('word');
        $query = Client::query()->whereNull('soft_deleted_at');

        if (is_string($word) && $word !== '') {
            $escaped = addcslashes($word, '%_\\');
            $query->where('company', 'like', '%' . $escaped . '%');
        }

        $clients = $query
            ->orderBy('company')
            ->get()
            ->map(fn (Client $client) => $this->resource($client));

        return response()->json(['data' => $clients]);
    }

    /**
     * クライアント企業を登録する。
     *
     * 旧 `client/register.blade.php`（`ClientsController@register`）相当。
     * `client_id` は UUIDv7 で自動採番する。失敗時は 422 JSON。
     */
    public function store(StoreClientApiRequest $request): JsonResponse
    {
        $safe = $request->validated();

        $client = Client::create([
            'client_id'      => (string) Uuid::uuid7(),
            'company'        => $safe['company'],
            'url'            => $safe['url'],
            'tel'            => $safe['tel'],
            'street_address' => $safe['street_address'],
            'note'           => $safe['note'] ?? null,
        ]);

        return response()->json([
            'data' => $this->resource($client),
        ], 201);
    }

    /**
     * クライアント詳細（担当者一覧込み）を返す。
     *
     * 旧 `client/client_detail.blade.php` 相当。
     */
    public function show(string $clientId): JsonResponse
    {
        $client = Client::with('contacts')
            ->where('client_id', $clientId)
            ->firstOrFail();

        return response()->json([
            'data' => array_merge($this->resource($client), [
                'contacts' => $client->contacts->map(fn (Contacts $contact) => [
                    'id'          => $contact->id,
                    'name'        => $contact->name,
                    'tel'         => $contact->tel,
                    'email'       => $contact->email,
                    'note'        => $contact->note,
                    'modified_at' => optional($contact->modified_at)->format('Y-m-d H:i:s'),
                ])->values(),
            ]),
        ]);
    }

    /**
     * クライアント企業の基本情報。
     *
     * @return array<string, mixed>
     */
    private function resource(Client $client): array
    {
        return [
            'client_id'      => $client->client_id,
            'company'        => $client->company,
            'url'            => $client->url,
            'tel'            => $client->tel,
            'post_code'      => $client->post_code,
            'street_address' => $client->street_address,
            'note'           => $client->note,
            'modified_at'    => optional($client->modified_at)->format('Y-m-d H:i:s'),
        ];
    }
}
