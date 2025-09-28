<?php

namespace App\Console\Commands;

use App\Models\Service;
use Illuminate\Console\Command;
use App\Models\Advertisement;
use Illuminate\Support\Facades\Log;

class UpdateAdvertisementStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'advertisement:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update advertisement status to expired when end_date has passed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = now()->toDateString();

        $expiredAds = Advertisement::where('end_date', '<', $today)
            ->where('status', '!=', 'expired')
            ->get();
            $serviceIds = $expiredAds->pluck('services')->flatten()->pluck('id')->unique();

        Advertisement::whereIn('id', $expiredAds->pluck('id'))->update(['status' => 'expired']);

        Service::whereIn('id', $serviceIds)->update(['is_advertised' => 0]);

        $this->info("Updated {$expiredAds->count()} advertisements to expired and set is_advertised to 0 for related services.");
        Log::info("Updated {$expiredAds->count()} advertisements to expired.");

    }
}
