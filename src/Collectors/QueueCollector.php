<?php

namespace Wardtech\Connector\Collectors;

use Illuminate\Support\Facades\Queue;
use Wardtech\Connector\Contracts\Collector;

/**
 * Queue health. Pending/failed counts are best-effort: some drivers (e.g. sync)
 * can't report a size, so we degrade to null rather than fail the whole report.
 */
class QueueCollector implements Collector
{
    public function key(): string
    {
        return 'queue';
    }

    public function collect(): array
    {
        $connection = config('queue.default');

        return [
            'default_connection' => $connection,
            'driver' => config("queue.connections.{$connection}.driver"),
            'pending_jobs' => $this->pendingJobs(),
            'failed_jobs' => $this->failedJobs(),
        ];
    }

    /**
     * Number of jobs waiting on the default connection's default queue.
     */
    protected function pendingJobs(): ?int
    {
        try {
            return Queue::size();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Number of jobs currently in the failed-jobs store.
     */
    protected function failedJobs(): ?int
    {
        try {
            $failer = app('queue.failer');

            return $failer ? count($failer->all()) : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
