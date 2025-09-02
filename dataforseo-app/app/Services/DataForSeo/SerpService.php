<?php

namespace App\Services\DataForSeo;

use Illuminate\Support\Facades\Log;

class SerpService
{
    protected DataForSeoClient $client;
    protected SerpAnalyzer $analyzer;

    public function __construct(DataForSeoClient $client, SerpAnalyzer $analyzer)
    {
        $this->client = $client;
        $this->analyzer = $analyzer;
    }

    public function getWebsitePosition(string $keyword, string $domain, int $locationCode, string $languageCode): array
    {
        $normalizedDomain = DomainUtils::normalize($domain);

        try {
            $result = $this->searchWithSdk($keyword, $locationCode, $languageCode);

            if ($result) {
                return $this->formatResponse($result, $keyword, $normalizedDomain);
            }
        } catch (\Throwable $e) {
            Log::info('SDK failed, trying HTTP fallback', [
                'error' => $e->getMessage(),
                'keyword' => $keyword,
                'domain' => $normalizedDomain
            ]);
        }

        try {
            $result = $this->searchWithHttp($keyword, $locationCode, $languageCode);

            if ($result) {
                return $this->formatResponse($result, $keyword, $normalizedDomain);
            }
        } catch (\Throwable $e) {
            Log::error('Both SDK and HTTP failed', [
                'error' => $e->getMessage(),
                'keyword' => $keyword,
                'domain' => $normalizedDomain
            ]);
        }

        return [
            'success' => false,
            'message' => 'Failed to fetch search results',
            'data' => $this->emptyResult($keyword, $normalizedDomain)
        ];
    }

    protected function searchWithSdk(string $keyword, int $locationCode, string $languageCode): ?array
    {
        $model = new \DFSClientV3\Models\SerpApi\SettingSerpLiveAdvanced();
        $model->setSe("google");
        $model->setSeType("organic");
        $model->setKeyword($keyword);
        $model->setLocationCode($locationCode);
        $model->setLanguageCode($languageCode);

        if (method_exists($model, 'setDevice')) $model->setDevice("desktop");
        if (method_exists($model, 'setOs')) $model->setOs("windows");
        if (method_exists($model, 'setNum')) $model->setNum(100);
        if (method_exists($model, 'setDepth')) $model->setDepth(100);

        $jsonRaw = method_exists($model, 'getAsJson') ? $model->getAsJson() : null;
        if ($jsonRaw) {
            $arr = json_decode($jsonRaw, true);
            $task = $arr['tasks'][0] ?? null;
            if (!$task || ($task['status_code'] ?? null) !== 20000) {
                throw new \RuntimeException($task['status_message'] ?? 'SDK task failed');
            }
        }

        $response = $model->get();

        if (!$response || !isset($response->tasks[0]->result[0]->items)) {
            return null;
        }

        return $response->tasks[0]->result[0]->items;
    }

    protected function searchWithHttp(string $keyword, int $locationCode, string $languageCode): ?array
    {
        $payload = [[
            'keyword' => $keyword,
            'location_code' => $locationCode,
            'language_code' => $languageCode,
            'device' => 'desktop',
            'os' => 'windows',
            'depth' => 100,
            'num' => 100,
        ]];

        $response = $this->client->httpRequest('serp/google/organic/live/advanced', $payload, 'POST');

        if (!$response['ok']) {
            throw new \RuntimeException('HTTP request failed: ' . $response['status']);
        }

        $task = $response['body']['tasks'][0] ?? null;
        if (!$task || ($task['status_code'] ?? null) !== 20000) {
            throw new \RuntimeException($task['status_message'] ?? 'HTTP task failed');
        }

        $items = $task['result'][0]['items'] ?? [];

        return array_map(fn($item) => (object) $item, $items);
    }

    protected function formatResponse($items, string $keyword, string $domain): array
    {
        $position = $this->analyzer->findDomainPosition($items, $domain);
        $peek = $this->analyzer->extractResultsPeek($items);

        return [
            'success' => true,
            'data' => [
                'keyword' => $keyword,
                'domain' => $domain,
                'found' => $position !== null,
                'position' => $position,
                'total_results' => is_countable($items) ? count($items) : 0,
                'peek' => $peek,
            ]
        ];
    }

    protected function emptyResult(string $keyword, string $domain): array
    {
        return [
            'keyword' => $keyword,
            'domain' => $domain,
            'found' => false,
            'position' => null,
            'total_results' => 0,
            'peek' => [],
        ];
    }
}
