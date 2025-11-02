<?php

declare(strict_types=1);

namespace Tourze\DifyCoreBundle\Tests\Controller\Admin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Tourze\DifyCoreBundle\Controller\Admin\ChatController;
use Tourze\DifyCoreBundle\Entity\DifyApp;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;

/**
 * Dify 聊天控制器测试
 * @internal
 */
#[CoversClass(ChatController::class)]
#[RunTestsInSeparateProcesses]
final class ChatControllerTest extends AbstractWebTestCase
{
    public function testChatRouteThrowsExceptionWhenSettingIdMissing(): void
    {
        $client = self::createAuthenticatedClient();

        $client->catchExceptions(false);
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('缺少必需的参数 settingId');

        $client->request('GET', '/admin/dify/chat');
    }

    public function testChatRouteThrowsExceptionWhenSettingNotFound(): void
    {
        $client = self::createAuthenticatedClient();

        $client->catchExceptions(false);
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Dify 配置 ID 999999 未找到');

        $client->request('GET', '/admin/dify/chat', ['settingId' => '999999']);
    }

    /**
     * @param array<string, string> $queryParams
     */
    #[DataProvider('provideInvalidSettingIds')]
    public function testChatRouteWithInvalidSettingIds(array $queryParams): void
    {
        $client = self::createAuthenticatedClient();

        $client->catchExceptions(false);
        $this->expectException(NotFoundHttpException::class);

        $client->request('GET', '/admin/dify/chat', $queryParams);
    }

    /**
     * @return array<string, array<int, array<string, string>>>
     */
    public static function provideInvalidSettingIds(): array
    {
        return [
            'non_existent_id' => [['settingId' => '999999']],
            'invalid_format' => [['settingId' => 'invalid-id']],
        ];
    }

    public function testChatRouteWithMissingSettingId(): void
    {
        $client = self::createAuthenticatedClient();

        $client->catchExceptions(false);
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('缺少必需的参数 settingId');

        $client->request('GET', '/admin/dify/chat');
    }

    public function testChatRouteWithEmptySettingId(): void
    {
        $client = self::createAuthenticatedClient();

        $client->catchExceptions(false);
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('缺少必需的参数 settingId');

        $client->request('GET', '/admin/dify/chat', ['settingId' => '']);
    }

    public function testChatRouteRequiresAuthentication(): void
    {
        $client = self::createClientWithDatabase();

        $client->catchExceptions(false);
        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage("Access Denied. The user doesn't have ROLE_ADMIN");

        $client->request('GET', '/admin/dify/chat', ['settingId' => '123']);
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createAuthenticatedClient();

        $client->catchExceptions(false);
        $this->expectException(MethodNotAllowedHttpException::class);

        $client->request($method, '/admin/dify/chat', ['settingId' => '123']);
    }

    protected function createAuthenticatedClient(): \Symfony\Bundle\FrameworkBundle\KernelBrowser
    {
        $client = self::createClientWithDatabase();

        // 创建具有管理员权限的用户
        $user = $this->createUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        return $client;
    }
}
