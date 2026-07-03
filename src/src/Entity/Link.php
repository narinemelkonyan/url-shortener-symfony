<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\LinkRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LinkRepository::class)]
#[ORM\Table(name: 'links')]
#[ORM\UniqueConstraint(name: 'uniq_link_url_hash', columns: ['url_hash'])]
#[ORM\UniqueConstraint(name: 'uniq_link_short_code', columns: ['short_code'])]
class Link
{
    public const string STATUS_PENDING = 'pending';
    public const string STATUS_READY = 'ready';
    public const int MAX_URL_LENGTH = 2048;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: self::MAX_URL_LENGTH)]
    private string $originalUrl;

    #[ORM\Column(length: 64)]
    private string $urlHash;

    #[ORM\Column(length: 8, nullable: true)]
    private ?string $shortCode = null;

    #[ORM\Column(length: 20)]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct(string $originalUrl, string $urlHash)
    {
        $this->originalUrl = $originalUrl;
        $this->urlHash = $urlHash;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOriginalUrl(): string
    {
        return $this->originalUrl;
    }

    public function getUrlHash(): string
    {
        return $this->urlHash;
    }

    public function getShortCode(): ?string
    {
        return $this->shortCode;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function isReady(): bool
    {
        return $this->status === self::STATUS_READY;
    }

    public function markAsReady(string $shortCode): void
    {
        $this->shortCode = $shortCode;
        $this->status = self::STATUS_READY;
    }
}
