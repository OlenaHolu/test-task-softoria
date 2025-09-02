<?php

namespace App\Services;

use App\Services\DataForSeo\MetadataService;
use App\Services\DataForSeo\SerpService;

class DataForSeoService
{
    protected SerpService $serpService;
    protected MetadataService $metadataService;

    public function __construct(
        SerpService $serpService,
        MetadataService $metadataService
    ) {
        $this->serpService = $serpService;
        $this->metadataService = $metadataService;
    }

    public function getWebsitePosition(string $keyword, string $domain, int $locationCode, string $languageCode): array
    {
        return $this->serpService->getWebsitePosition($keyword, $domain, $locationCode, $languageCode);
    }

    public function getLocations(): array
    {
        return $this->metadataService->getLocations();
    }

    public function getLanguages(): array
    {
        return $this->metadataService->getLanguages();
    }

    public function getLocationsMap(): array
    {
        return $this->metadataService->getLocationsMap();
    }

    public function getLanguagesMap(): array
    {
        return $this->metadataService->getLanguagesMap();
    }

    public function getDefaultLocationsMap(): array
    {
        return $this->metadataService->getDefaultLocationsMap();
    }

    public function getDefaultLanguagesMap(): array
    {
        return $this->metadataService->getDefaultLanguagesMap();
    }
}
