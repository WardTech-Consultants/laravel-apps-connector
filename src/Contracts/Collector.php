<?php

namespace Wardtech\Connector\Contracts;

interface Collector
{
    /**
     * The key this collector occupies in the status report payload.
     */
    public function key(): string;

    /**
     * Gather this collector's slice of the payload.
     *
     * @return array<string, mixed>|array<int, mixed>
     */
    public function collect(): array;
}
