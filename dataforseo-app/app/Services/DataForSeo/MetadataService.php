<?php

namespace App\Services\DataForSeo;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MetadataService
{
    protected DataForSeoClient $client;

    public function __construct(DataForSeoClient $client)
    {
        $this->client = $client;
    }

    public function getLocations(): array
    {
        return Cache::remember('dfs.locations', 86400, function () {
            try {
                $response = $this->client->httpRequest('serp/google/locations');
                
                if (!$response['ok']) {
                    return [];
                }

                $task = $response['body']['tasks'][0] ?? null;
                if (($task['status_code'] ?? null) !== 20000) {
                    return [];
                }

                return $task['result'] ?? [];
            } catch (\Throwable $e) {
                \Log::warning('Failed to fetch locations', ['error' => $e->getMessage()]);
                return [];
            }
        });
    }

    public function getLanguages(): array
    {
        return Cache::remember('dfs.languages', 86400, function () {
            try {
                $response = $this->client->httpRequest('serp/languages');
                
                if (!$response['ok']) {
                    return [];
                }

                $task = $response['body']['tasks'][0] ?? null;
                if (($task['status_code'] ?? null) !== 20000) {
                    return [];
                }

                return $task['result'] ?? [];
            } catch (\Throwable $e) {
                \Log::warning('Failed to fetch languages', ['error' => $e->getMessage()]);
                return [];
            }
        });
    }

    public function getLocationsMap(): array
    {
        $locations = $this->getLocations();
        if (empty($locations)) {
            return $this->getDefaultLocationsMap();
        }

        $map = [];
        foreach ($locations as $location) {
            $code = is_object($location) 
                ? (int)($location->location_code ?? 0) 
                : (int)($location['location_code'] ?? 0);
            $name = is_object($location) 
                ? (string)($location->location_name ?? '') 
                : (string)($location['location_name'] ?? '');
            
            if ($code && $name) {
                $map[$code] = $name;
            }
        }

        return $map ?: $this->getDefaultLocationsMap();
    }

    public function getLanguagesMap(): array
    {
        $languages = $this->getLanguages();
        if (empty($languages)) {
            return $this->getDefaultLanguagesMap();
        }

        $map = [];
        foreach ($languages as $language) {
            $code = is_object($language) 
                ? (string)($language->language_code ?? '') 
                : (string)($language['language_code'] ?? '');
            $name = is_object($language) 
                ? (string)($language->language_name ?? '') 
                : (string)($language['language_name'] ?? '');
            
            if ($code && $name) {
                $map[$code] = $name;
            }
        }

        return $map ?: $this->getDefaultLanguagesMap();
    }

    public function getDefaultLocationsMap(): array
    {
        return [
            2840 => 'United States',
            2826 => 'United Kingdom',
            2124 => 'Canada',
            2036 => 'Australia',
            2276 => 'Germany',
            2250 => 'France',
            2380 => 'Italy',
            2724 => 'Spain',
            2528 => 'Netherlands',
            2616 => 'Poland',
        ];
    }

    public function getDefaultLanguagesMap(): array
    {
        return [
            'en' => 'English',
            'es' => 'Spanish',
            'fr' => 'French',
            'de' => 'German',
            'it' => 'Italian',
            'pt' => 'Portuguese',
            'nl' => 'Dutch',
            'pl' => 'Polish',
            'ru' => 'Russian',
            'uk' => 'Ukrainian',
        ];
    }
}
