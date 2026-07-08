<?php

namespace Wardtech\Connector\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Wardtech\Connector\Collectors\ScheduleCollector;
use Wardtech\Connector\StatusReport;
use Wardtech\Connector\WardtechClient;

class ReportCommand extends Command
{
    protected $signature = 'wardtech:report
        {--dry-run : Build and display the payload without sending it}';

    protected $description = 'Send this application\'s status report to the Wardtech dashboard';

    public function handle(StatusReport $report, WardtechClient $client): int
    {
        $payload = $report->toArray();

        if ($this->option('dry-run')) {
            $this->line(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        if (! config('wardtech.enabled', true)) {
            $this->warn('Wardtech reporting is disabled (wardtech.enabled=false). Nothing sent.');

            return self::SUCCESS;
        }

        if (blank(config('wardtech.token'))) {
            $this->error('WARDTECH_TOKEN is not set. Add it to your .env before reporting.');

            return self::FAILURE;
        }

        try {
            $client->report($payload);
        } catch (\Throwable $e) {
            $this->error('Status report failed: '.$e->getMessage());

            return self::FAILURE;
        }

        Cache::forever(ScheduleCollector::CACHE_KEY, now()->toIso8601String());

        $this->info('Status report sent to '.config('wardtech.url'));

        return self::SUCCESS;
    }
}
