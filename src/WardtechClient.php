<?php

namespace Wardtech\Connector;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Thin HTTP client for the Wardtech central server. All requests are bearer
 * authenticated and made relative to the configured base URL.
 */
class WardtechClient
{
    /**
     * Send a full status report to the dashboard.
     */
    public function report(array $payload): Response
    {
        return $this->request()
            ->post('/api/status-report', $payload)
            ->throw();
    }

    /**
     * Verify connectivity + credentials. The server echoes back the registered
     * application so the caller can confirm which record it is talking to.
     */
    public function ping(): Response
    {
        return $this->request()
            ->post('/api/status-report/ping')
            ->throw();
    }

    protected function request(): PendingRequest
    {
        return Http::withToken(config('wardtech.token'))
            ->timeout((int) config('wardtech.timeout', 15))
            ->acceptJson()
            ->asJson()
            ->baseUrl(rtrim((string) config('wardtech.url'), '/'));
    }
}
