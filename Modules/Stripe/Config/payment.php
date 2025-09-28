<?php

return array (
  'name' => 'Stripe',
  'slug' => 'stripe',
  'image' => 'modules/stripe/images/logo.png',
  'title' => 'Stripe',
  'processing_fee' => 1.0,
  'subscription' => 0,
  'configs' => 
  array (
    'stripe_api_key' => 'REDACTED',
    'stripe_secret_key' => 'REDACTED',
    'stripe_webhook_secret_key' => '94E22264B76477432',
    'stripe_mode' => '1',
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
    'stripe_api_key' => 
    array (
      'type' => 'password',
      'label' => 'API Key',
    ),
    'stripe_secret_key' => 
    array (
      'type' => 'password',
      'label' => 'Secret Key',
    ),
    'stripe_webhook_secret_key' => 
    array (
      'type' => 'text',
      'label' => 'Webhook Secret Key',
    ),
    'subscription' => 
    array (
      'type' => 'checkbox',
      'label' => 'Subscription',
    ),
  ),
);
