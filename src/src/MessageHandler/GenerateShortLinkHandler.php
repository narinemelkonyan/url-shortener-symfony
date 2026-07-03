<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\GenerateShortLinkMessage;
use App\Repository\LinkRepository;
use App\Service\CodeGenerator;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Generates and persists a short code for a pending link.
 */
#[AsMessageHandler]
final readonly class GenerateShortLinkHandler
{
    private const int MAX_ATTEMPTS = 5;

    public function __construct(
        private LinkRepository $linkRepository,
        private EntityManagerInterface $entityManager,
        private CodeGenerator $codeGenerator,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(GenerateShortLinkMessage $message): void
    {
        $link = $this->linkRepository->find($message->linkId);

        if ($link === null) {
            $this->logger->warning('Link not found for generation.', ['linkId' => $message->linkId]);
            return;
        }

        if ($link->isReady()) {
            return;
        }

        for ($attempt = 1; $attempt <= self::MAX_ATTEMPTS; $attempt++) {
            $link->markAsReady($this->codeGenerator->generate());

            try {
                $this->entityManager->flush();
                return;
            } catch (UniqueConstraintViolationException) {
                $this->entityManager->clear();
                $link = $this->linkRepository->find($message->linkId);

                if ($link === null || $link->isReady()) {
                    return;
                }
            }
        }

        throw new \RuntimeException(
            sprintf('Failed to generate a unique short code after %d attempts.', self::MAX_ATTEMPTS)
        );
    }
}
