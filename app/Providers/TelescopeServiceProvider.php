<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;
use Laravel\Telescope\IncomingEntry;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Only enable Telescope if TELESCOPE_ENABLED=true
        if (config('app.telescope_enabled')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
        }
    }

    /**
     * Register the Telescope gate.
     */
    protected function gate(): void
    {
        Gate::define('viewTelescope', function ($user) {
            return $user && $user->email === 'iriogbepeter22@gmail.com';
        });
    }

    /**
     * Filter the entries Telescope records.
     */
    protected function authorization(): void
    {
        Telescope::filter(function (IncomingEntry $entry) {
            return $entry->isReportableException()
                || $entry->isFailedRequest()
                || $entry->isFailedJob()
                || $entry->isScheduledTask()
                || $entry->hasMonitoredTag();
        });
    }
}
