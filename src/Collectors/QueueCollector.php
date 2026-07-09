<?php

namespace Wardtech\Connector\Collectors;

use Illuminate\Support\Facades\DB;
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
            'jobs_table' => $this->jobsTable($connection),
        ];
    }

    /**
     * Direct read of the database queue's jobs table. Surfaces backlog and
     * stuck-worker signals that Queue::size() alone can't show. Returns null
     * when the app doesn't use a database queue or the table is unavailable.
     *
     * @return array<string, mixed>|null
     */
    protected function jobsTable(string $connection): ?array
    {
        try {
            $dbConnection = config("queue.connections.{$connection}.connection");
            $table = config("queue.connections.{$connection}.table", 'jobs');

            $db = DB::connection($dbConnection);

            if (! $db->getSchemaBuilder()->hasTable($table)) {
                return null;
            }

            $now = time();

            $reserved = (int) $db->table($table)->whereNotNull('reserved_at')->count();
            $delayed = (int) $db->table($table)->where('available_at', '>', $now)->count();
            $oldestAvailable = $db->table($table)
                ->whereNull('reserved_at')
                ->where('available_at', '<=', $now)
                ->min('created_at');

            return [
                'connection' => $dbConnection ?: config('database.default'),
                'table' => $table,
                'total' => (int) $db->table($table)->count(),
                'reserved' => $reserved,
                'delayed' => $delayed,
                // Age of the head-of-line job that a worker should have picked
                // up already — a rising number means workers aren't keeping up.
                'oldest_pending_seconds' => $oldestAvailable !== null ? max(0, $now - (int) $oldestAvailable) : null,
                'by_queue' => $db->table($table)
                    ->selectRaw('queue, count(*) as aggregate')
                    ->groupBy('queue')
                    ->pluck('aggregate', 'queue')
                    ->map(fn ($count) => (int) $count)
                    ->all(),
            ];
        } catch (\Throwable) {
            return null;
        }
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
