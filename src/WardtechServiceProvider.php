<?php

namespace Wardtech\Connector;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Wardtech\Connector\Commands\PingCommand;
use Wardtech\Connector\Commands\ReportCommand;

class WardtechServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/wardtech.php', 'wardtech');

        $this->app->singleton(WardtechClient::class);
        $this->app->singleton(StatusReport::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/wardtech.php' => config_path('wardtech.php'),
            ], 'wardtech-config');

            $this->commands([
                ReportCommand::class,
                PingCommand::class,
            ]);
        }

        if (config('wardtech.schedule.enabled', true)) {
            $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
                $schedule->command('wardtech:report')
                    ->dailyAt((string) config('wardtech.schedule.time', '03:00'))
                    ->withoutOverlapping();
            });
        }
    }
}
