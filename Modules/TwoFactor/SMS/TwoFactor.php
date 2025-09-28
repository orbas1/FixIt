<?php

namespace Modules\TwoFactor\SMS;

use Exception;
use App\Helpers\Helpers;

class TwoFactor
{

    public static function getIntent($sendTo, $message)
    {
        $api_key = env('TWOFACTOR_KEY');

        $url = 'https://2factor.in/API/R1/';

        $fields = [
            'module'       => 'TRANS_SMS',
            'apikey'       => $api_key,
            'to'           => $sendTo,
            'from'         => env(key: 'TWOFACTOR_SENDER_ID'),
            'msg'          => $message,
            'templatename' => env(key: 'TWOFACTOR_TEMPLATE_NAME')
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $err      = curl_error($ch);
      
        curl_close($ch);

        if (! empty($err)) {
            throw new Exception($err, 500);
        }

        return json_decode($response);
    }

}
