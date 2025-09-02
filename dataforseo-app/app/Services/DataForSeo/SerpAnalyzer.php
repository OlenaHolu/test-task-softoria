<?php

namespace App\Services\DataForSeo;

class SerpAnalyzer
{
    public function findDomainPosition($items, string $targetDomain): ?int
    {
        $targetDomain = DomainUtils::normalize($targetDomain);

        foreach ($items as $item) {
            $rank = $this->extractRank($item);
            if ($rank === null) {
                continue;
            }

            if ($this->isDomainMatch($item, $targetDomain)) {
                return (int) $rank;
            }
        }

        return null;
    }

    public function extractResultsPeek($items, int $limit = 20): array
    {
        $peek = [];
        foreach (array_slice($items, 0, $limit) as $item) {
            $peek[] = [
                'rank' => $this->extractRank($item),
                'url' => $this->extractUrl($item),
                'title' => $this->extractTitle($item),
                'type' => $this->extractType($item),
            ];
        }
        return $peek;
    }

    protected function extractRank($item): ?int
    {
        if (is_object($item)) {
            return $item->rank_absolute ?? $item->rank_group ?? null;
        }
        return $item['rank_absolute'] ?? $item['rank_group'] ?? null;
    }

    protected function extractUrl($item): ?string
    {
        if (is_object($item)) {
            return $item->url ?? $item->result_url ?? null;
        }
        return $item['url'] ?? $item['result_url'] ?? null;
    }

    protected function extractTitle($item): ?string
    {
        if (is_object($item)) {
            return $item->title ?? null;
        }
        return $item['title'] ?? null;
    }

    protected function extractType($item): ?string
    {
        if (is_object($item)) {
            return $item->type ?? null;
        }
        return $item['type'] ?? null;
    }

    protected function isDomainMatch($item, string $targetDomain): bool
    {
        $url = $this->extractUrl($item);
        if ($url) {
            $urlDomain = DomainUtils::extractFromUrl($url);
            if ($urlDomain === $targetDomain) {
                return true;
            }
        }

        $itemDomain = null;
        if (is_object($item)) {
            $itemDomain = $item->domain ?? null;
        } else {
            $itemDomain = $item['domain'] ?? null;
        }

        if ($itemDomain) {
            $normalizedItemDomain = DomainUtils::normalize($itemDomain);
            if ($normalizedItemDomain === $targetDomain) {
                return true;
            }
        }

        return false;
    }
}
