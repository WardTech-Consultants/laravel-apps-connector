<?php

namespace Wardtech\Connector\Collectors;

use Wardtech\Connector\Contracts\Collector;

/**
 * Migration state. A non-zero `pending` count flags a half-deployed app where
 * code shipped but migrations never ran. Migration filenames are not secret.
 */
class MigrationCollector implements Collector
{
    public function key(): string
    {
        return 'migrations';
    }

    public function collect(): array
    {
        try {
            $migrator = app('migrator');
            $repository = $migrator->getRepository();

            if (! $repository->repositoryExists()) {
                return ['status' => 'no_migration_table'];
            }

            $paths = array_merge([database_path('migrations')], $migrator->paths());
            $files = array_keys($migrator->getMigrationFiles($paths));
            $ran = $repository->getRan();
            $pending = array_values(array_diff($files, $ran));

            return [
                'status' => 'ok',
                'ran' => count($ran),
                'pending' => count($pending),
                'pending_migrations' => $pending,
            ];
        } catch (\Throwable $e) {
            return ['status' => 'unavailable', 'error' => $e->getMessage()];
        }
    }
}
