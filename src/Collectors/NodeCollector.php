<?php

namespace Wardtech\Connector\Collectors;

use Wardtech\Connector\Contracts\Collector;

/**
 * The installed npm package list, read straight from package-lock.json. Like
 * ComposerCollector we ship exact names + versions and let the central server
 * correlate them against the advisory database — so this client needs no node
 * or npm binary at runtime. Handles lockfile v2/v3 (the `packages` map) as well
 * as the legacy v1 (`dependencies`) shape.
 */
class NodeCollector implements Collector
{
    public function key(): string
    {
        return 'node_components';
    }

    public function collect(): array
    {
        $path = base_path('package-lock.json');

        if (! is_file($path)) {
            return [];
        }

        $lock = json_decode((string) file_get_contents($path), true) ?: [];

        // lockfile v2/v3: authoritative `packages` map keyed by install path.
        if (! empty($lock['packages'])) {
            return collect($lock['packages'])
                ->reject(fn ($meta, $path) => $path === '' || ! str_contains($path, 'node_modules/'))
                ->map(fn (array $meta, string $path) => [
                    'name' => substr($path, strrpos($path, 'node_modules/') + strlen('node_modules/')),
                    'version' => $meta['version'] ?? null,
                    'dev' => (bool) ($meta['dev'] ?? false),
                    'active' => true,
                ])
                ->filter(fn (array $pkg) => $pkg['version'] !== null)
                ->values()
                ->all();
        }

        // legacy lockfile v1: flat `dependencies` map keyed by package name.
        return collect($lock['dependencies'] ?? [])
            ->map(fn (array $meta, string $name) => [
                'name' => $name,
                'version' => $meta['version'] ?? null,
                'dev' => (bool) ($meta['dev'] ?? false),
                'active' => true,
            ])
            ->filter(fn (array $pkg) => $pkg['version'] !== null)
            ->values()
            ->all();
    }
}
