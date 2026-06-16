<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Contacts;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
