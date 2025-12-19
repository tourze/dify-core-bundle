<?php

declare(strict_types=1);

namespace Tourze\DifyCoreBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\SymfonyDependencyServiceLoader\AutoExtension;

final class DifyCoreExtension extends AutoExtension
{
    protected function getConfigDir(): string
    {
        return __DIR__ . '/../Resources/config';
    }
}
