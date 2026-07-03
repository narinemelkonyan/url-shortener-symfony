<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Service\UrlNormalizer;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for UrlNormalizer.
 */
final class UrlNormalizerTest extends TestCase
{
    private UrlNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new UrlNormalizer();
    }

    public function testTrailingSlashOnEmptyPathIsRemoved(): void
    {
        self::assertSame(
            $this->normalizer->normalize('http://example.com'),
            $this->normalizer->normalize('http://example.com/'),
        );
    }

    public function testSchemeAndHostAreLowercased(): void
    {
        self::assertSame(
            'https://example.com/Path',
            $this->normalizer->normalize('HTTPS://Example.COM/Path'),
        );
    }

    public function testDefaultPortIsRemoved(): void
    {
        self::assertSame(
            'http://example.com',
            $this->normalizer->normalize('http://example.com:80'),
        );
    }

    public function testNonDefaultPortIsKept(): void
    {
        self::assertSame(
            'http://example.com:8080',
            $this->normalizer->normalize('http://example.com:8080'),
        );
    }

    public function testEmptyUrlIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->normalizer->normalize('   ');
    }

    public function testUrlWithoutHostIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->normalizer->normalize('not-a-url');
    }

    public function testDisallowedSchemeIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->normalizer->normalize('ftp://example.com/file');
    }

    public function testTooLongUrlIsRejected(): void
    {
        $longUrl = 'http://example.com/' . str_repeat('a', 3000);
        $this->expectException(\InvalidArgumentException::class);
        $this->normalizer->normalize($longUrl);
    }
}
