<?php

namespace Database\Seeders;

use App\Models\CustomSmsGateway;
use Illuminate\Database\Seeder;

class CustomSmsGatewaySettingSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run()
    {
        $values = [
            'base_url' => 'https://api.twilio.com/2010-04-01/Accounts/{sid}/Messages.json',
            'method' => 'post',
            'is_config' => [
                'sid','auth_token'
            ],
            'sid' => 'Enter ID',
            'auth_token' => 'Enter Auth Token',
            'from' => '12345679',
            'body' => [
                'to' => '{to}',
                'from' => '123456798',
                'message' => '{message}'
            ]
        ];

        CustomSmsGateway::updateOrCreate([
            'base_url' => $values['base_url'],
            'method' => $values['method'],
            'is_config' => $values['is_config'],
            'sid' => $values['sid'],
            'auth_token' => $values['auth_token'],
            'from' => $values['from'],
            'body' => $values['body']
        ]);
    }
}
