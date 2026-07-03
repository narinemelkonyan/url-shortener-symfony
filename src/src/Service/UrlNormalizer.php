<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Link;

/**
 * Normalizes URLs to a canonical form.
 */
final class UrlNormalizer
{
    private const array ALLOWED_SCHEMES = ['http', 'https'];

    private const array DEFAULT_PORTS = [
        'http' => 80,
        'https' => 443,
    ];

    /**
     * Returns the canonical form of the given URL.
     *
     * @throws \InvalidArgumentException
     */
    public function normalize(string $url): string
    {
        $url = trim($url);

        if ($url === '') {
            throw new \InvalidArgumentException('URL must not be empty.');
        }

        if (strlen($url) > Link::MAX_URL_LENGTH) {
            throw new \InvalidArgumentException('URL is too long.');
        }

        $parts = parse_url($url);
        if ($parts === false || !isset($parts['host'])) {
            throw new \InvalidArgumentException('Invalid URL: unable to determine host.');
        }

        $scheme = isset($parts['scheme']) ? strtolower($parts['scheme']) : 'http';

        if (!in_array($scheme, self::ALLOWED_SCHEMES, true)) {
            throw new \InvalidArgumentException('Only http and https URLs are supported.');
        }

        $host = strtolower($parts['host']);

        $port = '';
        if (isset($parts['port']) && ($this->defaultPortFor($scheme) !== $parts['port'])) {
            $port = ':' . $parts['port'];
        }

        $path = $parts['path'] ?? '';
        if ($path === '/') {
            $path = '';
        }

        $query = isset($parts['query']) ? '?' . $parts['query'] : '';

        return sprintf('%s://%s%s%s%s', $scheme, $host, $port, $path, $query);
    }

    private function defaultPortFor(string $scheme): ?int
    {
        return self::DEFAULT_PORTS[$scheme] ?? null;
    }
}
