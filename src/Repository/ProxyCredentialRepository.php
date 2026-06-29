<?php

declare(strict_types=1);

namespace GeoProxy\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use GeoProxy\Entity\ProxyCredential;

/** @extends ServiceEntityRepository<ProxyCredential> */
final class ProxyCredentialRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProxyCredential::class);
    }

    public function save(ProxyCredential $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
