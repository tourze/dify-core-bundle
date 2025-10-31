<?php

declare(strict_types=1);

namespace Tourze\DifyCoreBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tourze\DifyCoreBundle\Entity\DifyApp;
use Tourze\DifyCoreBundle\Repository\DifyAppRepository;
use Tourze\DifyCoreBundle\Service\DifyAppService;

/**
 * DifyAppService 测试类
 * @internal
 */
#[CoversClass(DifyAppService::class)]
final class DifyAppServiceTest extends TestCase
{
    private DifyAppService $difyAppService;

    private DifyAppRepository&MockObject $difyAppRepository;

    protected function setUp(): void
    {
        $this->difyAppRepository = $this->createMock(DifyAppRepository::class);
        $this->difyAppService = new DifyAppService($this->difyAppRepository);
    }

    public function testFindByIdWithExistingApp(): void
    {
        $app = $this->createTestApp('test-app-1', 'Test API Key 1', 'https://api.dify.test');
        $appId = 'test-id-123';

        $this->difyAppRepository
            ->expects($this->once())
            ->method('find')
            ->with($appId)
            ->willReturn($app)
        ;

        $result = $this->difyAppService->findById($appId);

        $this->assertInstanceOf(DifyApp::class, $result);
        $this->assertEquals('test-app-1', $result->getName());
    }

    public function testFindByIdWithNonExistentApp(): void
    {
        $this->difyAppRepository
            ->expects($this->once())
            ->method('find')
            ->with('non-existent-id')
            ->willReturn(null)
        ;

        $result = $this->difyAppService->findById('non-existent-id');

        $this->assertNull($result);
    }

    public function testFindByNameWithExistingApp(): void
    {
        $app = $this->createTestApp('test-app-name', 'Test API Key', 'https://api.dify.test');

        $this->difyAppRepository
            ->expects($this->once())
            ->method('findByName')
            ->with('test-app-name')
            ->willReturn($app)
        ;

        $result = $this->difyAppService->findByName('test-app-name');

        $this->assertInstanceOf(DifyApp::class, $result);
        $this->assertEquals('test-app-name', $result->getName());
    }

    public function testFindByNameWithNonExistentApp(): void
    {
        $this->difyAppRepository
            ->expects($this->once())
            ->method('findByName')
            ->with('non-existent-name')
            ->willReturn(null)
        ;

        $result = $this->difyAppService->findByName('non-existent-name');

        $this->assertNull($result);
    }

    public function testFindValidApps(): void
    {
        $validApp1 = $this->createTestApp('valid-app-1', 'API Key 1', 'https://api.dify.test', true);
        $validApp2 = $this->createTestApp('valid-app-2', 'API Key 2', 'https://api.dify.test', true);
        $validApps = [$validApp1, $validApp2];

        $this->difyAppRepository
            ->expects($this->once())
            ->method('findBy')
            ->with(['valid' => true])
            ->willReturn($validApps)
        ;

        $result = $this->difyAppService->findValidApps();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals($validApps, $result);
    }

    public function testFindValidAppsWithNoValidApps(): void
    {
        $this->difyAppRepository
            ->expects($this->once())
            ->method('findBy')
            ->with(['valid' => true])
            ->willReturn([])
        ;

        $result = $this->difyAppService->findValidApps();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testGetAppsToSyncWithSpecificAppId(): void
    {
        $app = $this->createTestApp('sync-app', 'API Key', 'https://api.dify.test', true);
        $appId = 'test-app-id';

        $this->difyAppRepository
            ->expects($this->once())
            ->method('find')
            ->with($appId)
            ->willReturn($app)
        ;

        $result = $this->difyAppService->getAppsToSync($appId);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals($app, $result[0]);
    }

    public function testGetAppsToSyncWithNonExistentAppId(): void
    {
        $this->difyAppRepository
            ->expects($this->once())
            ->method('find')
            ->with('non-existent-id')
            ->willReturn(null)
        ;

        $result = $this->difyAppService->getAppsToSync('non-existent-id');

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testGetAppsToSyncWithInvalidApp(): void
    {
        $app = $this->createTestApp('invalid-sync-app', 'API Key', 'https://api.dify.test', false);
        $appId = 'invalid-app-id';

        $this->difyAppRepository
            ->expects($this->once())
            ->method('find')
            ->with($appId)
            ->willReturn($app)
        ;

        $result = $this->difyAppService->getAppsToSync($appId);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testGetAppsToSyncWithValidAppButNullIsValid(): void
    {
        $app = $this->createTestApp('null-valid-app', 'API Key', 'https://api.dify.test');
        $app->setValid(null);
        $appId = 'null-valid-app-id';

        $this->difyAppRepository
            ->expects($this->once())
            ->method('find')
            ->with($appId)
            ->willReturn($app)
        ;

        $result = $this->difyAppService->getAppsToSync($appId);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testGetAppsToSyncWithoutAppId(): void
    {
        $validApp1 = $this->createTestApp('valid-sync-1', 'API Key 1', 'https://api.dify.test', true);
        $validApp2 = $this->createTestApp('valid-sync-2', 'API Key 2', 'https://api.dify.test', true);
        $validApps = [$validApp1, $validApp2];

        $this->difyAppRepository
            ->expects($this->once())
            ->method('findBy')
            ->with(['valid' => true])
            ->willReturn($validApps)
        ;

        $result = $this->difyAppService->getAppsToSync();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals($validApps, $result);
    }

    public function testSaveWithFlush(): void
    {
        $app = $this->createTestApp('save-test-app', 'Save API Key', 'https://api.dify.test');

        $this->difyAppRepository
            ->expects($this->once())
            ->method('save')
            ->with($app, true)
        ;

        $this->difyAppService->save($app, true);
    }

    public function testSaveWithoutFlush(): void
    {
        $app = $this->createTestApp('save-no-flush-app', 'No Flush API Key', 'https://api.dify.test');

        $this->difyAppRepository
            ->expects($this->once())
            ->method('save')
            ->with($app, false)
        ;

        $this->difyAppService->save($app, false);
    }

    public function testRemoveWithFlush(): void
    {
        $app = $this->createTestApp('remove-test-app', 'Remove API Key', 'https://api.dify.test');

        $this->difyAppRepository
            ->expects($this->once())
            ->method('remove')
            ->with($app, true)
        ;

        $this->difyAppService->remove($app, true);
    }

    public function testRemoveWithoutFlush(): void
    {
        $app = $this->createTestApp('remove-no-flush-app', 'Remove No Flush API Key', 'https://api.dify.test');

        $this->difyAppRepository
            ->expects($this->once())
            ->method('remove')
            ->with($app, false)
        ;

        $this->difyAppService->remove($app, false);
    }

    /**
     * 创建测试用的 DifyApp 实体
     */
    private function createTestApp(string $name, string $apiKey, string $baseUrl, ?bool $valid = true): DifyApp
    {
        $app = new DifyApp();
        $app->setName($name);
        $app->setApiKey($apiKey);
        $app->setBaseUrl($baseUrl);
        $app->setValid($valid);
        $app->setDescription('Test description for ' . $name);

        return $app;
    }
}
