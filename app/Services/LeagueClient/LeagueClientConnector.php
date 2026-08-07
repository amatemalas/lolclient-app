<?php

namespace App\Services\LeagueClient;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
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
        $candidates = $this->config['lockfile_path']
            ? [$this->config['lockfile_path']]
            : ($this->config['lockfile_paths'][strtolower(PHP_OS_FAMILY)] ?? []);

        foreach ($candidates as $path) {
            if (is_file($path) && is_readable($path)) {
                return $path;
            }
        }

        return null;
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
     * Send a request to the LCU and return the decoded response body.
     *
     * @throws ClientNotRunningException
     */
    public function request(string $method, string $path): mixed
    {
        $client = $this->client();

        $response = match (strtoupper($method)) {
            'GET' => $client->get($path),
            'POST' => $client->post($path),
            'PUT' => $client->put($path),
            'PATCH' => $client->patch($path),
            'DELETE' => $client->delete($path),
            default => throw new InvalidArgumentException("Unsupported LCU method: {$method}"),
        };

        return $response->json();
    }
}
