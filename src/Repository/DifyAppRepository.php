<?php

declare(strict_types=1);

namespace Tourze\DifyCoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\DifyCoreBundle\Entity\DifyApp;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;

/**
 * @extends ServiceEntityRepository<DifyApp>
 */
#[AsRepository(entityClass: DifyApp::class)]
class DifyAppRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DifyApp::class);
    }

    public function findByName(string $name): ?DifyApp
    {
        return $this->findOneBy(['name' => $name]);
    }

    public function save(DifyApp $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DifyApp $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
