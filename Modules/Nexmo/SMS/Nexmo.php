<?php

namespace Modules\Nexmo\SMS;

use Exception;
use App\Helpers\Helpers;

class Nexmo
{

  public static function getIntent($message)
  {

    $nexmo_sid = env('NEXMO_SENDER_ID');
    $nexmo_key = env('NEXMO_KEY');
    $nexmo_secret = env('NEXMO_SECRET');

    $url = 'https://rest.nexmo.com/sms/json';

    $intent = [
      'api_key' => $nexmo_key,
      'api_secret' => $nexmo_secret,
      'to' => $message['to'],
      'from' => $nexmo_sid,
      'text' => $message['body'],
    ];


    $fields = http_build_query($intent);
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

    $response = curl_exec($ch);
    $err = curl_error($ch);
    $message_res = json_decode($response);
    curl_close($ch);
    if (!empty($err)) {
      throw new Exception($err, 500);
    }

    return $message_res;
  }
}
