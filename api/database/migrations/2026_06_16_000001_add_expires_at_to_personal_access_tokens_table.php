<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sanctum v4 のトークンモデルは `expires_at` 列に書き込むが、
 * 既存の personal_access_tokens 移行（2019 年作成・tokenable_id を文字列化する
 * カスタム版）にはこの列が無い。SPA トークン認証（Phase 1）を成立させるため、
 * 既存定義を壊さず列のみ追加する。
 */
return new class extends Migration
{
    public function up(): void
    {
        if (
            Schema::hasTable('personal_access_tokens')
            && ! Schema::hasColumn('personal_access_tokens', 'expires_at')
        ) {
            Schema::table('personal_access_tokens', function (Blueprint $table) {
                $table->timestamp('expires_at')->nullable()->after('last_used_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('personal_access_tokens', 'expires_at')) {
            Schema::table('personal_access_tokens', function (Blueprint $table) {
                $table->dropColumn('expires_at');
            });
        }
    }
};
