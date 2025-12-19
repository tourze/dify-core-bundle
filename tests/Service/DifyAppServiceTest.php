<?php

declare(strict_types=1);

namespace Tourze\DifyCoreBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\DifyCoreBundle\Entity\DifyApp;
use Tourze\DifyCoreBundle\Service\DifyAppService;

/**
 * DifyAppService 测试类
 * @internal
 */
#[CoversClass(DifyAppService::class)]
#[RunTestsInSeparateProcesses]
final class DifyAppServiceTest extends \Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase
{
    private DifyAppService $difyAppService;

    protected function onSetUp(): void
    {
        $this->difyAppService = self::getService(DifyAppService::class);
    }

    public function testServiceIsRegistered(): void
    {
        $this->assertInstanceOf(DifyAppService::class, $this->difyAppService);
    }

    public function testFindById(): void
    {
        // 创建测试数据
        $app = new DifyApp();
        $app->setName('Test App');
        $app->setApiKey('test-key');
        $app->setBaseUrl('https://test.com');
        $app->setValid(true);

        $this->difyAppService->save($app);

        // 测试查找存在的应用
        $foundApp = $this->difyAppService->findById($app->getId());
        $this->assertNotNull($foundApp);
        $this->assertEquals('Test App', $foundApp->getName());

        // 测试查找不存在的应用
        $notFoundApp = $this->difyAppService->findById('non-existent-id');
        $this->assertNull($notFoundApp);
    }

    public function testFindByName(): void
    {
        // 创建测试数据
        $app = new DifyApp();
        $app->setName('Unique Test App');
        $app->setApiKey('unique-key');
        $app->setBaseUrl('https://unique.com');
        $app->setValid(true);

        $this->difyAppService->save($app);

        // 测试查找存在的应用
        $foundApp = $this->difyAppService->findByName('Unique Test App');
        $this->assertNotNull($foundApp);
        $this->assertEquals($app->getId(), $foundApp->getId());

        // 测试查找不存在的应用
        $notFoundApp = $this->difyAppService->findByName('Non-existent App');
        $this->assertNull($notFoundApp);
    }

    public function testFindValidApps(): void
    {
        // 不需要手动清理，测试会自动隔离

        // 获取初始有效应用数量
        $initialValidApps = $this->difyAppService->findValidApps();
        $initialCount = count($initialValidApps);

        // 创建测试数据 - 有效应用
        $validApp1 = new DifyApp();
        $validApp1->setName('Valid App 1');
        $validApp1->setApiKey('valid-key-1');
        $validApp1->setBaseUrl('https://valid1.com');
        $validApp1->setValid(true);

        $validApp2 = new DifyApp();
        $validApp2->setName('Valid App 2');
        $validApp2->setApiKey('valid-key-2');
        $validApp2->setBaseUrl('https://valid2.com');
        $validApp2->setValid(true);

        // 创建测试数据 - 无效应用
        $invalidApp = new DifyApp();
        $invalidApp->setName('Invalid App');
        $invalidApp->setApiKey('invalid-key');
        $invalidApp->setBaseUrl('https://invalid.com');
        $invalidApp->setValid(false);

        $this->difyAppService->save($validApp1);
        $this->difyAppService->save($validApp2);
        $this->difyAppService->save($invalidApp);

        // 测试查找有效应用
        $validApps = $this->difyAppService->findValidApps();
        $this->assertCount($initialCount + 2, $validApps);

        $validAppNames = array_map(fn($app) => $app->getName(), $validApps);
        $this->assertContains('Valid App 1', $validAppNames);
        $this->assertContains('Valid App 2', $validAppNames);
        $this->assertNotContains('Invalid App', $validAppNames);
    }

    public function testGetAppsToSyncWithNullAppId(): void
    {
        // 不需要手动清理，测试会自动隔离

        // 获取初始有效应用数量
        $initialValidApps = $this->difyAppService->getAppsToSync();
        $initialCount = count($initialValidApps);

        // 创建测试数据
        $validApp = new DifyApp();
        $validApp->setName('Valid App For Sync');
        $validApp->setApiKey('sync-key');
        $validApp->setBaseUrl('https://sync.com');
        $validApp->setValid(true);

        $invalidApp = new DifyApp();
        $invalidApp->setName('Invalid App For Sync');
        $invalidApp->setApiKey('invalid-sync-key');
        $invalidApp->setBaseUrl('https://invalid-sync.com');
        $invalidApp->setValid(false);

        $this->difyAppService->save($validApp);
        $this->difyAppService->save($invalidApp);

        // 测试获取所有有效应用进行同步
        $appsToSync = $this->difyAppService->getAppsToSync();
        $this->assertCount($initialCount + 1, $appsToSync);

        $appNames = array_map(fn($app) => $app->getName(), $appsToSync);
        $this->assertContains('Valid App For Sync', $appNames);
        $this->assertNotContains('Invalid App For Sync', $appNames);
    }

    public function testGetAppsToSyncWithSpecificAppId(): void
    {
        // 创建测试数据
        $validApp = new DifyApp();
        $validApp->setName('Valid App Specific');
        $validApp->setApiKey('specific-key');
        $validApp->setBaseUrl('https://specific.com');
        $validApp->setValid(true);

        $invalidApp = new DifyApp();
        $invalidApp->setName('Invalid App Specific');
        $invalidApp->setApiKey('invalid-specific-key');
        $invalidApp->setBaseUrl('https://invalid-specific.com');
        $invalidApp->setValid(false);

        $this->difyAppService->save($validApp);
        $this->difyAppService->save($invalidApp);

        // 测试获取特定的有效应用进行同步
        $appsToSync = $this->difyAppService->getAppsToSync($validApp->getId());
        $this->assertCount(1, $appsToSync);
        $this->assertEquals('Valid App Specific', $appsToSync[0]->getName());

        // 测试获取特定的无效应用进行同步
        $appsToSync = $this->difyAppService->getAppsToSync($invalidApp->getId());
        $this->assertCount(0, $appsToSync);

        // 测试获取不存在的应用进行同步
        $appsToSync = $this->difyAppService->getAppsToSync('non-existent-id');
        $this->assertCount(0, $appsToSync);
    }

    public function testSave(): void
    {
        $app = new DifyApp();
        $app->setName('Save Test App');
        $app->setApiKey('save-key');
        $app->setBaseUrl('https://save.com');
        $app->setValid(true);

        // 测试保存新实体
        $this->difyAppService->save($app);

        // 验证实体已保存
        $this->assertNotNull($app->getId());

        $savedApp = $this->difyAppService->findById($app->getId());
        $this->assertNotNull($savedApp);
        $this->assertEquals('Save Test App', $savedApp->getName());
    }

    public function testSaveWithoutFlush(): void
    {
        $app = new DifyApp();
        $app->setName('Save No Flush Test App');
        $app->setApiKey('save-no-flush-key');
        $app->setBaseUrl('https://save-no-flush.com');
        $app->setValid(true);

        // 测试保存但不立即刷新
        $this->difyAppService->save($app, false);

        // 手动刷新以验证数据已保存
        $entityManager = self::getEntityManager();
        $entityManager->flush();

        // 验证实体已保存
        $this->assertNotNull($app->getId());
    }

    public function testRemove(): void
    {
        $app = new DifyApp();
        $app->setName('Remove Test App');
        $app->setApiKey('remove-key');
        $app->setBaseUrl('https://remove.com');
        $app->setValid(true);

        // 先保存实体
        $this->difyAppService->save($app);
        $appId = $app->getId();
        $this->assertNotNull($appId);

        // 验证实体存在
        $existingApp = $this->difyAppService->findById($appId);
        $this->assertNotNull($existingApp);

        // 测试删除实体
        $this->difyAppService->remove($app);

        // 验证实体已删除
        $deletedApp = $this->difyAppService->findById($appId);
        $this->assertNull($deletedApp);
    }

    public function testRemoveWithoutFlush(): void
    {
        $app = new DifyApp();
        $app->setName('Remove No Flush Test App');
        $app->setApiKey('remove-no-flush-key');
        $app->setBaseUrl('https://remove-no-flush.com');
        $app->setValid(true);

        // 先保存实体
        $this->difyAppService->save($app);
        $appId = $app->getId();
        $this->assertNotNull($appId);

        // 测试删除但不立即刷新
        $this->difyAppService->remove($app, false);

        // 手动刷新以验证删除操作
        $entityManager = self::getEntityManager();
        $entityManager->flush();

        // 验证实体已删除
        $deletedApp = $this->difyAppService->findById($appId);
        $this->assertNull($deletedApp);
    }
}
