<?php

declare(strict_types=1);

namespace Tourze\DifyCoreBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Tourze\DifyCoreBundle\Entity\DifyApp;

/**
 * DifyApp 数据填充器
 */
final class DifyAppFixtures extends Fixture
{
    public const DIFY_APP_DEFAULT_REFERENCE = 'dify-app-default';

    public function load(ObjectManager $manager): void
    {
        $difyApp = new DifyApp();
        $difyApp->setName('默认Dify应用');
        $difyApp->setDescription('系统默认的Dify AI应用配置');
        $difyApp->setApiKey('your-dify-api-key-here');
        $difyApp->setBaseUrl('https://api.dify.ai');
        $difyApp->setValid(true);

        $manager->persist($difyApp);
        $this->addReference(self::DIFY_APP_DEFAULT_REFERENCE, $difyApp);

        $manager->flush();
    }
}
