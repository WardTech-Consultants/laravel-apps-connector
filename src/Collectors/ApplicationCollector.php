<?php

namespace Wardtech\Connector\Collectors;

use Wardtech\Connector\Contracts\Collector;

/**
 * Basic identity of the application. The URL is already public, and the name
 * is whatever you set in config/app.php — nothing sensitive here.
 */
class ApplicationCollector implements Collector
{
    public function key(): string
    {
        return 'application';
    }

    public function collect(): array
    {
        return [
            'name' => config('app.name'),
            'url' => config('app.url'),
            'locale' => config('app.locale'),
            'timezone' => config('app.timezone'),
        ];
    }
}
