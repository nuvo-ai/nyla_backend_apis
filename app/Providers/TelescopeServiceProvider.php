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
        // Enable Telescope only if TELESCOPE_ENABLED=true
        if ($this->app->environment('local') || config('app.telescope_enabled')) {
            Telescope::night();
        }
    }

    /**
     * Register the Telescope gate.
     */
    protected function gate(): void
    {
        Gate::define('viewTelescope', function ($user) {
            // Only allow this specific email
            return $user && in_array($user->email, [
                'iriogbepeter22@gmail.com',
            ]);
        });
    }

    /**
     * Filter the entries Telescope records.
     */
    protected function authorization(): void
    {
        Telescope::filter(function (IncomingEntry $entry) {
            // Always log reportable entries
            return $entry->isReportableException()
                || $entry->isFailedRequest()
                || $entry->isFailedJob()
                || $entry->isScheduledTask()
                || $entry->hasMonitoredTag();
        });
    }
}
