<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Link;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Link>
 */
class LinkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Link::class);
    }

    /**
     * Finds a link by the hash of its normalized original URL.
     */
    public function findOneByUrlHash(string $urlHash): ?Link
    {
        return $this->findOneBy(['urlHash' => $urlHash]);
    }

    /**
     * Finds a link by its generated short code.
     */
    public function findOneByShortCode(string $shortCode): ?Link
    {
        return $this->findOneBy(['shortCode' => $shortCode]);
    }
}
