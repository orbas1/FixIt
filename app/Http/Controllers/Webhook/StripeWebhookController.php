<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Services\Payments\EscrowPaymentIntentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Symfony\Component\HttpFoundation\Response;
use UnexpectedValueException;

class StripeWebhookController extends Controller
{
    public function __construct(private readonly EscrowPaymentIntentService $paymentIntents)
    {
    }

    public function __invoke(Request $request): Response
    {
        $secret = config('services.stripe.webhook_secret');
        if (! $secret) {
            return response()->json(['error' => 'Webhook secret missing'], 500);
        }

        $payload = $request->getContent();
        $signature = (string) $request->header('Stripe-Signature', '');

        try {
            $event = Webhook::constructEvent($payload, $signature, $secret);
        } catch (UnexpectedValueException|SignatureVerificationException $exception) {
            Log::warning('Stripe webhook signature validation failed', [
                'error' => $exception->getMessage(),
            ]);

            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $object = $event->data->object->toArray();

        switch ($event->type) {
            case 'payment_intent.succeeded':
                $this->paymentIntents->handleSucceededPaymentIntent($object);
                break;
            case 'payment_intent.payment_failed':
                $this->paymentIntents->handleFailedPaymentIntent($object);
                break;
            default:
                Log::debug('Unhandled Stripe webhook event', ['type' => $event->type]);
                break;
        }

        return response()->json(['received' => true]);
    }
}
