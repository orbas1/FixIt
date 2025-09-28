<?php

namespace App\Http\Traits;

use Exception;
use App\Messages\Twilio;
use App\Enums\SMSMethod;
use App\Exceptions\ExceptionHandler;
use App\Helpers\Helpers;

trait MessageTrait
{

  public function getIntent($message)
  {
    try {

      $settings = Helpers::getSmsGatewaySettings();
      $url = $settings['base_url'] ?? null;
      $sid = $settings['sid'] ?? null;
      $auth_token = $settings['auth_token'] ?? null;
      $from = $settings['from'] ?? null;
      
      
      if (!$url || !$sid || !$auth_token || !$from) {
        throw new Exception("SMS gateway settings are incomplete.", 400);
      }
      
      $urlParams = $settings['params'];
      
      if (!empty($urlParams)) {
        $filteredUrlParams = $this->filter($urlParams, $message);
        $url .= '?' . http_build_query($filteredUrlParams);
      }
      
      if ($settings['body']) {
        
        $decodedBody = is_array($settings['body']) 
        ? $settings['body'] 
        : json_decode($settings['body'], true);
        
        if (empty($decodedBody) || !is_array($decodedBody)) {
          throw new Exception("Body is not in json Format.", 400);
        }
        
      }
      $bodyTemplate = $this->filter($decodedBody, $message);
      
      
      
      
      $headers = [];
      if (!empty($settings['headers'])) {
        if (is_array($settings['headers'])) {
          foreach ($settings['headers'] as $key => $value) {
            $headers[] = "$key: $value";
          }
        }
      }
      
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      
      if ($sid && $auth_token) {
        curl_setopt($ch, CURLOPT_USERPWD, "$sid:$auth_token");
      }
      
      curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
      
      if (strtolower($settings['method']) === 'post') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($bodyTemplate));
      }
      
      if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      }
      
      $res = curl_exec($ch);
      $err = curl_error($ch);
      $response = json_decode($res);
     
      curl_close($ch);
      if (!empty($err)) {
        throw new Exception($err, 500);
      }
      
      return $response;

    } catch (Exception $e) {

      throw new ExceptionHandler($e->getMessage(), $e->getCode());
    }
  }

  public function filter($data, $message)
  {
    return array_map(function ($param) use ($message) {
      $cleanedString = str_replace(['{', '}'], '', $param);
      return $message[$cleanedString] ?? $param;
    }, $data);
  }

  public function sendMessage($message)
  {
    try {

      return $this->getIntent($message);
      
    } catch (Exception $e) {

      throw new ExceptionHandler($e->getMessage(), $e->getCode());
    }
  }
}
