<?php

namespace App\Console\Commands;

use App\Enums\BookingEnumSlug;
use Illuminate\Console\Command;
use App\Models\Booking;
use App\Models\BookingStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CancelPastPendingBookings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'booking:cancel-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancel all pending bookings with date_time before today';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();

        $pendingStatusId = BookingStatus::where('slug', BookingEnumSlug::PENDING)->value('id');
        $cancelledStatusId = BookingStatus::where('slug', BookingEnumSlug::COMPLETED)->value('id');

        if (!$pendingStatusId || !$cancelledStatusId) {
            $this->error('Required booking statuses (pending/cancelled) not found.');
            return;
        }

        $expiredBookings = Booking::where('booking_status_id', $pendingStatusId)
            ->whereDate('date_time', '<', $today)
            ->get();

        $count = $expiredBookings->count();

        foreach ($expiredBookings as $booking) {
            $booking->booking_status_id = $cancelledStatusId;
            $booking->save();
        }

        Log::info("Cron: Cancelled {$count} expired pending bookings on " . now()->toDateTimeString());
        $this->info("Cancelled {$count} expired pending bookings.");
    }
}
