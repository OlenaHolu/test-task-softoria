<?php

namespace App\Services\DataForSeo;

use DFSClientV3\DFSClient;
use Illuminate\Support\Facades\Http;

class DataForSeoClient
{
    protected DFSClient $client;
    protected array $config;

    public function __construct()
    {
        $this->config = [
            'login' => (string) config('services.dataforseo.login'),
            'password' => (string) config('services.dataforseo.password'),
            'url' => rtrim((string) config('services.dataforseo.url', 'https://api.dataforseo.com'), '/'),
            'version' => '/' . trim((string) config('services.dataforseo.version', 'v3'), '/') . '/',
            'timeout' => (int) config('services.dataforseo.timeout', 120),
        ];

        if (empty($this->config['login']) || empty($this->config['password'])) {
            throw new \RuntimeException('DataForSEO credentials are missing');
        }

        $this->initializeClient();
    }

    protected function initializeClient(): void
    {
        $this->client = new DFSClient();
        $this->client->setConfig([
            'DATAFORSEO_LOGIN' => $this->config['login'],
            'DATAFORSEO_PASSWORD' => $this->config['password'],
            'apiVersion' => $this->config['version'],
            'url' => $this->config['url'],
            'timeoutForEachRequests' => $this->config['timeout'],
            'extraEntitiesPaths' => [],
            'login' => $this->config['login'],
            'password' => $this->config['password'],
        ]);
    }

    public function getClient(): DFSClient
    {
        return $this->client;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function httpRequest(string $endpoint, array $payload = [], string $method = 'GET'): array
    {
        $url = $this->config['url'] . $this->config['version'] . ltrim($endpoint, '/');
        
        $request = Http::withBasicAuth($this->config['login'], $this->config['password'])
            ->timeout($this->config['timeout'])
            ->retry(1, 500, throw: false);

        $response = match(strtoupper($method)) {
            'POST' => $request->post($url, $payload),
            'GET' => $request->get($url, $payload),
            default => throw new \InvalidArgumentException("Unsupported method: $method")
        };

        return [
            'ok' => $response->successful(),
            'status' => $response->status(),
            'body' => $response->json() ?? $response->body()
        ];
    }
}
