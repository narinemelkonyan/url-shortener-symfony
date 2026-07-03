<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Link;
use App\Message\GenerateShortLinkMessage;
use App\Repository\LinkRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Orchestrates the "shorten or fetch" use case for a given original URL.
 */
final readonly class LinkService
{
    public function __construct(
        private LinkRepository $linkRepository,
        private EntityManagerInterface $entityManager,
        private MessageBusInterface $messageBus,
        private UrlNormalizer $urlNormalizer,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Returns the link for the given original URL, creating a pending one
     * if it does not exist yet.
     *
     * @throws \InvalidArgumentException
     */
    public function shortenOrFetch(string $originalUrl): Link
    {
        $normalized = $this->urlNormalizer->normalize($originalUrl);
        $urlHash = hash('sha256', $normalized);

        $existing = $this->linkRepository->findOneByUrlHash($urlHash);
        if ($existing !== null) {
            return $existing;
        }

        return $this->createPendingLink($normalized, $urlHash);
    }

    /**
     * Returns the ready link for the given short code, or null if it does not
     * exist or has not been generated yet.
     */
    public function findReadyByCode(string $code): ?Link
    {
        $link = $this->linkRepository->findOneByShortCode($code);

        if ($link === null || !$link->isReady()) {
            return null;
        }

        return $link;
    }

    private function createPendingLink(string $normalizedUrl, string $urlHash): Link
    {
        $link = new Link($normalizedUrl, $urlHash);

        try {
            $this->entityManager->persist($link);
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException) {
            $this->entityManager->clear();

            $winner = $this->linkRepository->findOneByUrlHash($urlHash);
            if ($winner === null) {
                $this->logger->error('Link disappeared after a unique-constraint violation.', [
                    'urlHash' => $urlHash,
                ]);

                throw new \RuntimeException('Link disappeared after a unique-constraint violation.');
            }

            $this->logger->info('Concurrent insert detected; returning the existing link.', [
                'urlHash' => $urlHash,
            ]);

            return $winner;
        }

        $this->messageBus->dispatch(new GenerateShortLinkMessage($link->getId()));

        return $link;
    }
}
