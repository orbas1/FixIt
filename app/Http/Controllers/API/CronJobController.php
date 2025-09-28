<?php 

namespace App\Http\Controllers\API;

use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Exceptions\ExceptionHandler;
use Illuminate\Support\Facades\Artisan;

class CronJobController extends Controller
{
    /**
     * To Send Email and SMS Run Cronjob Type Wise
     */
    public function notification(Request $request)
    {
        try {
            $type = $request->get('type');
            $queueMap = [
                'createBookingEvent'            => 'createBookingEvent',
                'updateBookingStatusEvent'      => 'updateBookingStatusEvent',
                'assignBooking'            => 'assignBooking',
                'createProvider'           => 'createProvider',
                'extraChargeEvent'         => 'extraChargeEvent',
                'createBid'                => 'createBid',
                'createServicemanWithdraw' => 'createServicemanWithdraw',
                'createWithdrawReques'    => 'createWithdrawReques',
                'UpdateBidEvent'    => 'UpdateBidEvent',
                'UpdateServiceProofEvent'    => 'UpdateServiceProofEvent',
                'addServiceProofEvent'    => 'addServiceProofEvent',
                'BookingReminderEvent'    => 'UpdateServiceProofEvent',
                'createProvider'    => 'createProvider',
                'createServiceRequest'    => 'createServiceRequest',
            ];
            // Artisan::call('queue:work', [
            //     '--queue' => 'createBooking',
            //     '--once' => true
            // ]);

            // Artisan::call('queue:work', [
            //     '--queue' => 'updateBookingStatus',
            //     '--once' => true
            // ]);

            // Artisan::call('queue:work', [
            //     '--queue' => 'assingBooking',
            //     '--once' => true
            // ]);

            // Artisan::call('queue:work', [
            //     '--queue' => 'createProvider',
            //     '--once' => true
            // ]);

            // Artisan::call('queue:work', [
            //     '--queue' => 'extraChargeEvent',
            //     '--once' => true
            // ]);

            // Artisan::call('queue:work', [
            //     '--queue' => 'createBid',
            //     '--once' => true
            // ]);

            // Artisan::call('queue:work', [
            //     '--queue' => 'createServicemanWithdraw',
            //     '--once' => true
            // ]);

            // Artisan::call('queue:work', [
            //     '--queue' => 'createWithdrawReques',
            //     '--once' => true
            // ]);

            if ($type && isset($queueMap[$type])) {
                // Run only the queue for this type once
                Artisan::call('queue:work', [
                    '--queue' => $queueMap[$type],
                    '--once'  => true
                ]);

                return response()->json([
                    'status' => true,
                    'message' => "Processed queue: {$queueMap[$type]}",
                ]);
            }

             return response()->json([
                'status' => false,
                'message' => 'Invalid or missing type parameter',
            ], 400);            

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

}