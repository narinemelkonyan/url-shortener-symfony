<?php

declare(strict_types=1);

namespace App\Message;

/**
 * Instructs a worker to generate a short code for an already-persisted link.
 */
final readonly class GenerateShortLinkMessage
{
    public function __construct(
        public int $linkId,
    ) {
    }
}
