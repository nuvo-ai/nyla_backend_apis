<?php

namespace App\Console\Commands;

use App\Models\General\Subscription;
use Illuminate\Console\Command;
use Carbon\Carbon;

class UpdateExpiredSubscriptions extends Command
{
    protected $signature = 'subscriptions:update-expired';
    protected $description = 'Update all expired subscriptions to expired status';

    public function handle()
    {
        $now = Carbon::now();

        $updated = Subscription::where('status', '!=', 'expired')
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', $now)
            ->update(['status' => 'expired']);

        $this->info("Updated {$updated} expired subscriptions.");
    }
}
