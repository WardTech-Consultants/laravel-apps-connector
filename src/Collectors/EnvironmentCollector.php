<?php

namespace Wardtech\Connector\Collectors;

use Illuminate\Support\Facades\App;
use Wardtech\Connector\Contracts\Collector;

/**
 * Deployment posture and safety flags. `debug` being true in production is the
 * single most valuable signal here — it is a common, high-impact misconfig.
 */
class EnvironmentCollector implements Collector
{
    public function key(): string
    {
        return 'environment';
    }

    public function collect(): array
    {
        return [
            'env' => App::environment(),
            'debug' => (bool) config('app.debug'),
            'maintenance_mode' => App::isDownForMaintenance(),
            'config_cached' => App::configurationIsCached(),
            'routes_cached' => App::routesAreCached(),
            'events_cached' => App::eventsAreCached(),
        ];
    }
}
