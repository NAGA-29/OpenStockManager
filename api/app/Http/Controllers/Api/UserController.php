<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserApiRequest;
use App\Http\Requests\UpdateUserApiRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * 管理者向けユーザー管理 API（admin ミドルウェアで保護）。
 */
class UserController extends Controller
{
    /**
     * ユーザー一覧。
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query()->orderBy('id');

        if ($request->filled('word')) {
            $keyword = '%' . addcslashes($request->word, '%_\\') . '%';
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', $keyword)
                    ->orWhere('email', 'like', $keyword);
            });
        }

        $users = $query->paginate(10);

        return response()->json([
            'data' => collect($users->items())->map(fn (User $user) => $this->userResource($user)),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    /**
     * ユーザー登録。
     */
    public function store(StoreUserApiRequest $request): JsonResponse
    {
        try {
            $safe = $request->safe()->all();

            $user = User::create([
                'name' => $safe['name'],
                'email' => $safe['email'],
                'password' => Hash::make($safe['password']),
                'role' => $safe['role'],
            ]);

            return response()->json([
                'data' => $this->userResource($user),
                'message' => 'ユーザーを登録しました。',
            ], 201);
        } catch (\Exception $err) {
            Log::channel('error')->error('User registration failed', [
                'error_message' => $err->getMessage(),
            ]);

            return response()->json([
                'message' => 'ユーザー登録に失敗しました。',
                'error' => $err->getMessage(),
            ], 500);
        }
    }

    /**
     * ユーザー更新。
     */
    public function update(UpdateUserApiRequest $request, int $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => '指定されたユーザーが見つかりません。',
            ], 404);
        }

        try {
            $safe = $request->safe()->all();

            $user->name = $safe['name'];
            $user->email = $safe['email'];
            $user->role = $safe['role'];

            if (!empty($safe['password'])) {
                $user->password = Hash::make($safe['password']);
            }

            $user->save();

            return response()->json([
                'data' => $this->userResource($user),
                'message' => 'ユーザー情報を更新しました。',
            ]);
        } catch (\Exception $err) {
            Log::channel('error')->error('User update failed', [
                'target_user_id' => $id,
                'error_message' => $err->getMessage(),
            ]);

            return response()->json([
                'message' => 'ユーザー更新に失敗しました。',
                'error' => $err->getMessage(),
            ], 500);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function userResource(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'is_admin' => $user->isAdmin(),
            'created_at' => optional($user->created_at)->format('Y-m-d'),
        ];
    }
}
