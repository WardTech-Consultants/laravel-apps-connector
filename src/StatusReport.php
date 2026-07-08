<?php

namespace Wardtech\Connector;

use Illuminate\Contracts\Support\Arrayable;
use Wardtech\Connector\Collectors\ApplicationCollector;
use Wardtech\Connector\Collectors\ComposerCollector;
use Wardtech\Connector\Collectors\EnvironmentCollector;
use Wardtech\Connector\Collectors\MigrationCollector;
use Wardtech\Connector\Collectors\QueueCollector;
use Wardtech\Connector\Collectors\ScheduleCollector;
use Wardtech\Connector\Collectors\ServerCollector;
use Wardtech\Connector\Contracts\Collector;

/**
 * Assembles the full status report payload from the registered collectors.
 * A failure in any single collector is isolated so it can never break the
 * whole report — that slice is replaced with an { error } marker instead.
 */
class StatusReport implements Arrayable
{
    /**
     * @var array<int, class-string<Collector>>
     */
    protected array $collectors = [
        ApplicationCollector::class,
        EnvironmentCollector::class,
        ServerCollector::class,
        ComposerCollector::class,
        QueueCollector::class,
        MigrationCollector::class,
        ScheduleCollector::class,
    ];

    public function toArray(): array
    {
        $payload = [
            'type' => 'laravel',
            'core_version' => app()->version(),
            'reported_at' => now()->toIso8601String(),
        ];

        foreach ($this->collectors as $class) {
            /** @var Collector $collector */
            $collector = app($class);

            try {
                $payload[$collector->key()] = $collector->collect();
            } catch (\Throwable $e) {
                $payload[$collector->key()] = ['error' => $e->getMessage()];
            }
        }

        return $payload;
    }
}
