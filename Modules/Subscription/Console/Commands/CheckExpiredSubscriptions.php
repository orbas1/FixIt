<?php

namespace Modules\Subscription\Console\Commands;

use Illuminate\Console\Command;
use Modules\Subscription\Entities\UserSubscription;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CheckExpiredSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'subscription:check-expired';

    /**
     * The console command description.
     */
    protected $description = 'Check and update expired provider subscriptions';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredSubscriptions = UserSubscription::where('end_date', '<', now())->get();
        foreach ($expiredSubscriptions as $subscription) {
            $subscription->update(['is_active' => false]);
        }

        $this->info('Expired subscriptions checked and updated successfully.');
    }

    /**
     * Get the console command arguments.
     */
    protected function getArguments(): array
    {
        return [
            ['example', InputArgument::REQUIRED, 'An example argument.'],
        ];
    }

    /**
     * Get the console command options.
     */
    protected function getOptions(): array
    {
        return [
            ['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
        ];
    }
}
