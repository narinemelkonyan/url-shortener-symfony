<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Entity\Link;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Functional tests for the /api/shortlink endpoint.
 */
final class ShortLinkControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $this->entityManager->createQuery('DELETE FROM ' . Link::class)->execute();
    }

    public function testInvalidUrlReturns400(): void
    {
        $this->client->request('GET', '/api/shortlink', ['url' => 'ftp://example.com/file']);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testMissingUrlReturns400(): void
    {
        $this->client->request('GET', '/api/shortlink');

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testNewUrlReturnsReadyWithSyncTransport(): void
    {
        $this->client->request('GET', '/api/shortlink', ['url' => 'https://example.com/new']);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertSame('ready', $data['status']);
        self::assertNotEmpty($data['short_code']);
    }

    public function testSameUrlCreatesOnlyOneRecord(): void
    {
        $this->client->request('GET', '/api/shortlink', ['url' => 'https://example.com/dup']);
        $this->client->request('GET', '/api/shortlink', ['url' => 'https://example.com/dup']);

        $count = (int) $this->entityManager
            ->createQuery('SELECT COUNT(l.id) FROM ' . Link::class . ' l')
            ->getSingleScalarResult();

        self::assertSame(1, $count);
    }

    public function testShortCodeHasValidFormat(): void
    {
        $this->client->request('GET', '/api/shortlink', ['url' => 'https://example.com/format']);

        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertMatchesRegularExpression('/^[a-zA-Z0-9]{4,8}$/', $data['short_code']);
    }
}
