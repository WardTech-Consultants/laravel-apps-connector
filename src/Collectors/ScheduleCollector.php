<?php

namespace Wardtech\Connector\Collectors;

use Illuminate\Support\Facades\Cache;
use Wardtech\Connector\Contracts\Collector;

/**
 * Scheduler heartbeat. Reports when this app last successfully sent a report so
 * the dashboard can detect apps whose cron/scheduler has silently died. The
 * timestamp is written by ReportCommand after a successful send.
 */
class ScheduleCollector implements Collector
{
    public const CACHE_KEY = 'wardtech:last_reported_at';

    public function key(): string
    {
        return 'schedule';
    }

    public function collect(): array
    {
        return [
            'schedule_enabled' => (bool) config('wardtech.schedule.enabled', true),
            'last_reported_at' => Cache::get(self::CACHE_KEY),
        ];
    }
}
