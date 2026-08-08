<?php

namespace App\Services\LeagueClient;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class LeagueClientConnector
{
    /**
     * @param  array<string, mixed>|null  $config
     */
    public function __construct(protected ?array $config = null)
    {
        $this->config ??= config('leagueclient');
    }

    /**
     * Resolve the path to a readable lockfile, or null when the client is not
     * running (or not installed in a known location).
     */
    public function lockfilePath(): ?string
    {
        return (new LockfileResolver($this->config))->resolve();
    }

    /**
     * Read and parse the lockfile into LCU credentials.
     *
     * @return array{pid: int, port: int, token: string, protocol: string}
     *
     * @throws ClientNotRunningException
     */
    public function credentials(): array
    {
        $path = $this->lockfilePath();

        if ($path === null) {
            throw ClientNotRunningException::noLockfile();
        }

        $contents = trim((string) file_get_contents($path));

        // LeagueClient:<pid>:<port>:<token>:<protocol>
        if (! preg_match('/^([^:]+):(\d+):(\d+):([^:]+):(\w+)$/', $contents, $matches)) {
            throw ClientNotRunningException::unreadable($path, 'malformed lockfile contents');
        }

        return [
            'pid' => (int) $matches[2],
            'port' => (int) $matches[3],
            'token' => $matches[4],
            'protocol' => $matches[5],
        ];
    }

    /**
     * Build an authenticated HTTP client for the local LCU API.
     *
     * @throws ClientNotRunningException
     */
    public function client(): PendingRequest
    {
        $credentials = $this->credentials();

        return Http::baseUrl($credentials['protocol'].'://127.0.0.1:'.$credentials['port'])
            ->withBasicAuth('riot', $credentials['token'])
            ->withoutVerifying()
            ->acceptJson()
            ->timeout($this->config['timeout'])
            ->connectTimeout($this->config['connect_timeout']);
    }

    /**
     * Build an authenticated HTTP client that requests raw bytes instead of
     * JSON, used to proxy binary assets (profile icons, champion squares, ...).
     *
     * @throws ClientNotRunningException
     */
    public function rawClient(): PendingRequest
    {
        return $this->client()->withHeaders(['Accept' => '*/*']);
    }

    /**
     * Send a request to the LCU and return the raw response so callers can
     * inspect status codes and error payloads.
     *
     * @param  array<string, mixed>  $body
     * @param  array<string, mixed>  $query
     *
     * @throws ClientNotRunningException
     */
    public function send(string $method, string $path, array $body = [], array $query = []): Response
    {
        $client = $this->client();

        $response = match (strtoupper($method)) {
            'GET' => $client->get($path, $query),
            'POST' => $client->post($path, $body),
            'PUT' => $client->put($path, $body),
            'PATCH' => $client->patch($path, $body),
            'DELETE' => $client->delete($path, $body),
            default => throw new InvalidArgumentException("Unsupported LCU method: {$method}"),
        };

        if (! $response->successful()) {
            Log::warning('LCU request failed', [
                'method' => $method,
                'path' => $path,
                'body' => $body,
                'status' => $response->status(),
                'response' => $this->truncate($response->body()),
            ]);
        }

        return $response;
    }

    private function truncate(string $body, int $limit = 2000): string
    {
        if (strlen($body) <= $limit) {
            return $body;
        }

        return substr($body, 0, $limit).'…';
    }

    /**
     * Send a request to the LCU and return the decoded response body.
     *
     * @param  array<string, mixed>  $body
     * @param  array<string, mixed>  $query
     *
     * @throws ClientNotRunningException
     */
    public function request(string $method, string $path, array $body = [], array $query = []): mixed
    {
        return $this->send($method, $path, $body, $query)->json();
    }
}
