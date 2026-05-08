<?php

namespace App\Services;

class AmazonTrackingService
{
    /**
     * Append Amazon tracking ID (tag) to the URL.
     */
    public function appendTrackingId(string $url, string $trackingId): string
    {
        $parsedUrl = parse_url($url);
        
        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $queryParams);
            $queryParams['tag'] = $trackingId;
            $parsedUrl['query'] = http_build_query($queryParams);
        } else {
            $parsedUrl['query'] = "tag=" . urlencode($trackingId);
        }

        return $this->unparseUrl($parsedUrl);
    }

    /**
     * Reconstruct a URL from its parsed components.
     */
    private function unparseUrl(array $parsedUrl): string
    {
        $scheme   = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] . '://' : '';
        $host     = isset($parsedUrl['host']) ? $parsedUrl['host'] : '';
        $port     = isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '';
        $user     = isset($parsedUrl['user']) ? $parsedUrl['user'] : '';
        $pass     = isset($parsedUrl['pass']) ? ':' . $parsedUrl['pass']  : '';
        $pass     = ($user || $pass) ? "$pass@" : '';
        $path     = isset($parsedUrl['path']) ? $parsedUrl['path'] : '';
        $query    = isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '';
        $fragment = isset($parsedUrl['fragment']) ? '#' . $parsedUrl['fragment'] : '';

        return "$scheme$user$pass$host$port$path$query$fragment";
    }
}
