<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * SPA(React)向けのトークンベース認証 API。
 * Laravel Sanctum の Personal Access Token を発行する。
 */
class AuthController extends Controller
{
    /**
     * ログインしてアクセストークンを発行する。
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        // 既存トークンを破棄して再発行（単一セッション運用）
        $user->tokens()->delete();
        $token = $user->createToken('spa')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $this->userResource($user),
        ]);
    }

    /**
     * 現在のユーザー情報を返す。
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $this->userResource($request->user()),
        ]);
    }

    /**
     * ログアウト（現在のトークンを破棄）。
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'logged_out']);
    }

    /**
     * @return array<string, mixed>
     */
    private function userResource(User $user): array
    {
        return [
            'id'       => $user->id,
            'name'     => $user->name,
            'email'    => $user->email,
            'role'     => $user->role,
            'is_admin' => $user->isAdmin(),
        ];
    }
}
