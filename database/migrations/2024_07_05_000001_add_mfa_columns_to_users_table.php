<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $anchor = 'default_payment_method_id';
        if (! Schema::hasColumn('users', $anchor)) {
            $anchor = Schema::hasColumn('users', 'stripe_customer_id')
                ? 'stripe_customer_id'
                : 'remember_token';
        }

        Schema::table('users', function (Blueprint $table) use ($anchor) {
            $table->string('mfa_secret')->nullable()->after($anchor);
            $table->string('mfa_pending_secret')->nullable()->after('mfa_secret');
            $table->timestamp('mfa_pending_secret_created_at')->nullable()->after('mfa_pending_secret');
            $table->timestamp('mfa_enabled_at')->nullable()->after('mfa_pending_secret_created_at');
            $table->timestamp('mfa_last_used_at')->nullable()->after('mfa_enabled_at');
            $table->json('mfa_recovery_codes')->nullable()->after('mfa_last_used_at');
            $table->timestamp('mfa_recovery_codes_generated_at')->nullable()->after('mfa_recovery_codes');
            $table->string('mfa_enforcement_level')->nullable()->after('mfa_recovery_codes_generated_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'mfa_secret',
                'mfa_pending_secret',
                'mfa_pending_secret_created_at',
                'mfa_enabled_at',
                'mfa_last_used_at',
                'mfa_recovery_codes',
                'mfa_recovery_codes_generated_at',
                'mfa_enforcement_level',
            ]);
        });
    }
};
