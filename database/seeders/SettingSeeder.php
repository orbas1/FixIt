<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        if (! Schema::hasColumns('settings', ['key', 'value'])) {
            return;
        }

        if (DB::table('settings')->exists()) {
            return;
        }

        DB::table('settings')->insert([
            [
                'key' => 'app.name',
                'value' => 'FixIt',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
