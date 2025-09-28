<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Review;
use App\Models\Service;
use App\Models\User;
use App\Models\CommissionHistory;
use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Enums\BookingEnumSlug;

class UpdateDateCommand extends Command
{
    protected $signature = 'fixit:date-update';
    protected $description = 'Update booking created_at dates based on booking status';

    public function handle()
    {
        $bookings = Booking::whereNull('deleted_at')->get();
        foreach ($bookings as $booking) {

            $status = $booking->booking_status->slug;
            $newCreatedAt = Carbon::now();

            if ($status === BookingEnumSlug::PENDING) {
                $baseDate = Carbon::tomorrow()->addDays(rand(0, 1));
                $newCreatedAt = $baseDate->copy()->addSeconds(rand(0, 86400 - 1));
            } elseif ($status === BookingEnumSlug::COMPLETED) {
                $baseDate = Carbon::yesterday()->subDays(rand(0, 1));
                $newCreatedAt = $baseDate->copy()->addSeconds(rand(0, 86400 - 1));
                CommissionHistory::where('booking_id', $booking->id)->update([
                    'created_at' => $newCreatedAt,
                ]);
            } else {
                $newCreatedAt = Carbon::today()->copy()->addSeconds(rand(0, 86400 - 1));
            }

            $booking->date_time = $newCreatedAt;

            $booking->created_at = Carbon::now()?->subDays(rand(1, 2));
            $booking->save();


        }

        $services = Service::whereNull('deleted_at')->get();

        foreach ($services as $service) {
            $randomDate = Carbon::now()->subDays(rand(1, 2));
            $service->created_at = $randomDate;
            $service->save();
        }

        $users = User::whereNull('deleted_at')->get();

        foreach ($users as $user) {
            $randomDate = Carbon::now()->subDays(rand(1, 2));

            $user->timestamps = false;
            $user->created_at = $randomDate;
            $user->save();
        }


        $reviews = Review::whereNull('deleted_at')->get();

        foreach ($reviews as $review) {
            $randomDate = Carbon::now()->subDays(rand(1, 2));

            $review->timestamps = false;
            $review->created_at = $randomDate;
            $review->save();
        }

        $this->info('✅ dates updated');
    }
}
