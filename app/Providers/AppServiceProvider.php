<?php

namespace App\Providers;

use App\Console\Commands\RunScheduledReports;
use App\Listeners\AuthEventSubscriber;
use App\Services\LogSmsGateway;
use App\Services\NullSmsGateway;
use App\Services\SmsGateway;
use App\Services\TwilioSmsGateway;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SmsGateway::class, function () {
            $driver = config('sms.driver', 'log');

            return match ($driver) {
                'twilio' => new TwilioSmsGateway(),
                'null' => new NullSmsGateway(),
                default => new LogSmsGateway(),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->commands([
            RunScheduledReports::class,
        ]);

        Event::subscribe(AuthEventSubscriber::class);
    }
}
