<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            ThemeOptionSeeder::class,
            CountriesSeeder::class,
            StateSeeder::class,
            CurrencySeeder::class,
            RoleSeeder::class,
            LanguageSeeder::class,
            TimeZoneSeeder::class,
            BookingStatusSeeder::class,
            SystemLangSeeder::class,
            SettingSeeder::class,
            HomePageSeeder::class,
            CustomSmsGatewaySettingSeeder::class
        ]);
    }
}
