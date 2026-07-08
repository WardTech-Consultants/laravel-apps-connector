<?php

namespace Wardtech\Connector\Collectors;

use Wardtech\Connector\Contracts\Collector;

/**
 * The installed package list, read straight from composer.lock. We deliberately
 * ship exact names + versions and let the central server correlate them against
 * the security-advisory database — so CVE logic lives in one place you control,
 * and this client needs no composer binary at runtime.
 */
class ComposerCollector implements Collector
{
    public function key(): string
    {
        return 'components';
    }

    public function collect(): array
    {
        $path = base_path('composer.lock');

        if (! is_file($path)) {
            return [];
        }

        $lock = json_decode((string) file_get_contents($path), true) ?: [];

        return collect($lock['packages'] ?? [])
            ->map(fn (array $package) => [
                'name' => $package['name'],
                'version' => $package['version'],
                'active' => true,
            ])
            ->values()
            ->all();
    }
}
