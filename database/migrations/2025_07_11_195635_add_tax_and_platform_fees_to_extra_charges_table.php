<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('extra_charges', function (Blueprint $table) {
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('platform_fees', 10, 2)->default(0);
            $table->decimal('grand_total', 10, 2)->default(0)->after('platform_fees');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('extra_charges', function (Blueprint $table) {
            $table->dropColumn('tax_amount');
            $table->dropColumn('platform_fees');
            $table->dropColumn('grand_total');
        });
    }
};
