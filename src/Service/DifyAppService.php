<?php

declare(strict_types=1);

namespace Tourze\DifyCoreBundle\Service;

use Tourze\DifyCoreBundle\Entity\DifyApp;
use Tourze\DifyCoreBundle\Repository\DifyAppRepository;

class DifyAppService
{
    public function __construct(
        private readonly DifyAppRepository $difyAppRepository,
    ) {
    }

    public function findById(string $id): ?DifyApp
    {
        return $this->difyAppRepository->find($id);
    }

    public function findByName(string $name): ?DifyApp
    {
        return $this->difyAppRepository->findByName($name);
    }

    /**
     * @return DifyApp[]
     */
    public function findValidApps(): array
    {
        return $this->difyAppRepository->findBy(['valid' => true]);
    }

    /**
     * @return DifyApp[]
     */
    public function getAppsToSync(?string $appId = null): array
    {
        if (null !== $appId) {
            $app = $this->findById($appId);

            return (null !== $app && null !== $app->isValid() && $app->isValid()) ? [$app] : [];
        }

        return $this->findValidApps();
    }

    public function save(DifyApp $entity, bool $flush = true): void
    {
        $this->difyAppRepository->save($entity, $flush);
    }

    public function remove(DifyApp $entity, bool $flush = true): void
    {
        $this->difyAppRepository->remove($entity, $flush);
    }
}
