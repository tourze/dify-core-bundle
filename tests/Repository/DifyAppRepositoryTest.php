<?php

declare(strict_types=1);

namespace Tourze\DifyCoreBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\DifyCoreBundle\Entity\DifyApp;
use Tourze\DifyCoreBundle\Repository\DifyAppRepository;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * DifyAppRepository 测试类
 * @internal
 */
#[CoversClass(DifyAppRepository::class)]
#[RunTestsInSeparateProcesses]
final class DifyAppRepositoryTest extends AbstractRepositoryTestCase
{
    protected function onSetUp(): void
    {
        // 基础设置由父类处理，这里可以添加特定的设置逻辑
    }

    protected function createNewEntity(): DifyApp
    {
        $app = new DifyApp();
        $app->setName('test-app-' . uniqid());
        $app->setBaseUrl('https://api.dify.ai');
        $app->setApiKey('test-key-' . uniqid());
        $app->setValid(true);

        return $app;
    }

    /**
     * @return DifyAppRepository
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return self::getService(DifyAppRepository::class);
    }

    public function testFindByNameWithExistingApp(): void
    {
        $app = $this->createNewEntity();
        $this->getRepository()->save($app, true);

        $result = $this->getRepository()->findByName($app->getName());

        $this->assertInstanceOf(DifyApp::class, $result);
        $this->assertEquals($app->getName(), $result->getName());
    }

    public function testFindByNameWithNonExistingApp(): void
    {
        $result = $this->getRepository()->findByName('non-existing-app-' . uniqid());

        $this->assertNull($result);
    }
}
