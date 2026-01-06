<?php

namespace App\Providers;

use App\Console\Commands\RunScheduledReports;
use App\Listeners\AuthEventSubscriber;
use App\Repositories\Contracts\ServiceRequestRepositoryInterface;
use App\Repositories\ServiceRequestRepository;
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
        // SMS Gateway binding
        $this->app->singleton(SmsGateway::class, function () {
            $driver = config('sms.driver', 'log');

            return match ($driver) {
                'twilio' => new TwilioSmsGateway(),
                'null' => new NullSmsGateway(),
                default => new LogSmsGateway(),
            };
        });

        // Repository bindings
        $this->app->bind(
            ServiceRequestRepositoryInterface::class,
            ServiceRequestRepository::class
        );
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

        // Zap Schedule Event Listeners
        if (class_exists(\Zap\Events\ScheduleCreated::class)) {
            Event::listen(
                \Zap\Events\ScheduleCreated::class,
                \App\Listeners\SendScheduleConfirmation::class
            );
        }

        if (class_exists(\Zap\Events\ScheduleDeleted::class)) {
            Event::listen(
                \Zap\Events\ScheduleDeleted::class,
                \App\Listeners\SendCancellationNotification::class
            );
        }
    }
}
