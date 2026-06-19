<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactApiRequest;
use App\Models\Contacts;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * 担当者の一覧を返す。
     *
     * 旧 `contacts/lists.blade.php`（`getAllContacts`）相当。`word` クエリで
     * 担当者名の部分一致検索が可能。所属企業名（`company`）を併せて返す。
     */
    public function index(Request $request): JsonResponse
    {
        $word  = $request->query('word');
        $query = Contacts::query()
            ->with('client')
            ->whereNull('soft_deleted_at');

        if (is_string($word) && $word !== '') {
            $escaped = addcslashes($word, '%_\\');
            $query->where('name', 'like', '%' . $escaped . '%');
        }

        $contacts = $query
            ->orderBy('name')
            ->get()
            ->map(fn (Contacts $contact) => $this->resource($contact));

        return response()->json(['data' => $contacts]);
    }

    /**
     * 担当者を登録する。
     *
     * 旧 `contacts/register.blade.php` 相当。成功時は 201＋作成リソース。
     */
    public function store(StoreContactApiRequest $request): JsonResponse
    {
        $safe = $request->validated();

        $contact = Contacts::create([
            'client_id' => $safe['client_id'],
            'name'      => $safe['name'],
            'email'     => $safe['email'],
            'tel'       => $safe['tel'],
            'note'      => $safe['note'] ?? null,
        ]);

        return response()->json([
            'data' => $this->resource($contact),
        ], 201);
    }

    /**
     * 担当者詳細を返す。
     *
     * 旧 `contacts/detail.blade.php`（`contactDetail`）相当。未知 ID は 404。
     */
    public function show(string $contactId): JsonResponse
    {
        $contact = Contacts::with('client')
            ->whereNull('soft_deleted_at')
            ->where('id', $contactId)
            ->firstOrFail();

        return response()->json(['data' => $this->resource($contact)]);
    }

    /**
     * 担当者の基本情報（所属企業名込み）。
     *
     * @return array<string, mixed>
     */
    private function resource(Contacts $contact): array
    {
        return [
            'id'          => $contact->id,
            'client_id'   => $contact->client_id,
            'company'     => optional($contact->client)->company,
            'name'        => $contact->name,
            'tel'         => $contact->tel,
            'email'       => $contact->email,
            'note'        => $contact->note,
            'modified_at' => optional($contact->modified_at)->format('Y-m-d H:i:s'),
        ];
    }
}
