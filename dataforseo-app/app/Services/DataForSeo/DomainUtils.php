<?php

namespace App\Services\DataForSeo;

class DomainUtils
{
    public static function normalize(string $domain): string
    {
        $domain = strtolower(trim($domain));
        $domain = preg_replace('~^https?://~i', '', $domain);
        $domain = preg_replace('~/.*$~', '', $domain);
        $domain = preg_replace('~:\d+$~', '', $domain);
        
        if (function_exists('idn_to_ascii')) {
            $ascii = idn_to_ascii($domain, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
            if ($ascii) {
                $domain = $ascii;
            }
        }
        
        return preg_replace('/^www\./', '', $domain);
    }

    public static function extractFromUrl(string $url): string
    {
        $host = parse_url($url, PHP_URL_HOST) ?: $url;
        return self::normalize($host);
    }

    public static function isValid(string $domain): bool
    {
        $normalized = self::normalize($domain);
        return (bool) preg_match('~^[a-z0-9.-]+\.[a-z]{2,}$~i', $normalized);
    }
}