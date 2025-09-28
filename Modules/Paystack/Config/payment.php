<?php

return array (
  'name' => 'Paystack',
  'slug' => 'paystack',
  'image' => 'modules/paystack/images/logo.png',
  'title' => 'Paystack',
  'processing_fee' => 1.0,
  'subscription' => 0,
  'configs' => 
  array (
    'paystack_public_key' => env('PAYSTACK_PUBLIC_KEY',''),
    'paystack_secret_key' => env('PAYSTACK_SECRET_KEY',''),
    'paystack_sandbox_mode' => '1',
  ),
  'fields' => 
  array (
    'title' => 
    array (
      'type' => 'text',
      'label' => 'Label',
    ),
    'processing_fee' => 
    array (
      'type' => 'number',
      'label' => 'Processing Fee',
    ),
    'paystack_sandbox_mode' => 
    array (
      'type' => 'select',
      'label' => 'Select Mode',
      'options' => 
      array (
        1 => 'Sandbox',
        0 => 'Live',
      ),
    ),
    'paystack_public_key' => 
    array (
      'type' => 'password',
      'label' => 'Public Key',
      'default' => 'paystack_public_key',
    ),
    'paystack_secret_key' => 
    array (
      'type' => 'password',
      'label' => 'Secret Key',
      'default' => 'paystack_secret_key',
    ),
    'subscription' => 
    array (
      'type' => 'checkbox',
      'label' => 'Subscription',
    ),
  ),
);
