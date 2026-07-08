<?php

namespace Wardtech\Connector\Collectors;

use Wardtech\Connector\Contracts\Collector;

/**
 * PHP and host information. Lets the dashboard spot EOL PHP versions or missing
 * extensions across the fleet. None of this is secret.
 */
class ServerCollector implements Collector
{
    public function key(): string
    {
        return 'server';
    }

    public function collect(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'os' => php_uname('s').' '.php_uname('r'),
            'hostname' => gethostname() ?: null,
            'sapi' => PHP_SAPI,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'extensions' => get_loaded_extensions(),
        ];
    }
}
