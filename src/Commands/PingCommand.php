<?php

namespace Wardtech\Connector\Commands;

use Illuminate\Console\Command;
use Wardtech\Connector\WardtechClient;

class PingCommand extends Command
{
    protected $signature = 'wardtech:ping';

    protected $description = 'Verify connectivity and credentials against the Wardtech dashboard';

    public function handle(WardtechClient $client): int
    {
        if (blank(config('wardtech.token'))) {
            $this->error('WARDTECH_TOKEN is not set. Add it to your .env before pinging.');

            return self::FAILURE;
        }

        try {
            $response = $client->ping();
        } catch (\Throwable $e) {
            $this->error('Ping failed: '.$e->getMessage());

            return self::FAILURE;
        }

        $name = $response->json('application.name');

        if (blank($name)) {
            $this->warn('Connected, but the server did not return application.name.');

            return self::SUCCESS;
        }

        // The server should send a scalar name, but guard against a non-string
        // (e.g. a nested structure) so a successful ping never crashes on the
        // string interpolation below.
        if (! is_scalar($name)) {
            $name = json_encode($name);
        }

        $this->info("Connected to Wardtech as: {$name}");

        return self::SUCCESS;
    }
}
