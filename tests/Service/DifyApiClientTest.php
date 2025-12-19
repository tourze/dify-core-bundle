<?php

declare(strict_types=1);

namespace Tourze\DifyCoreBundle\Tests\Service;

use HttpClientBundle\Request\RequestInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Tourze\DifyCoreBundle\Entity\DifyApp;
use Tourze\DifyCoreBundle\Exception\DifyApiException;
use Tourze\DifyCoreBundle\Exception\InvalidDifyConfigException;
use Tourze\DifyCoreBundle\Service\DifyApiClient;


#[CoversClass(DifyApiClient::class)]
#[RunTestsInSeparateProcesses]
final class DifyApiClientTest extends AbstractIntegrationTestCase
{
    private DifyApiClient $apiClient;

    protected function onSetUp(): void
    {
        $this->apiClient = self::getService(DifyApiClient::class);
    }

    public function testServiceIsRegistered(): void
    {
        $this->assertInstanceOf(DifyApiClient::class, $this->apiClient);
    }

    public function testRequestThrowsExceptionWhenNoAppSet(): void
    {
        $request = $this->createMock(RequestInterface::class);

        $this->expectException(InvalidDifyConfigException::class);
        $this->expectExceptionMessage('请先通过 setApp() 设置 Dify 应用');

        $this->apiClient->request($request);
    }

    /**
     * 测试 request() 方法与有效应用
     * 使用测试专用的 MockHttpClient 服务
     */
    public function testRequestWithValidApp(): void
    {
        $app = new DifyApp();
        $app->setName('Test App');
        $app->setApiKey('test-key');
        $app->setBaseUrl('https://test.com');
        $app->setValid(true);

        // 获取测试专用的 MockHttpClient
        $mockHttpClient = self::getServiceById('Test.MockHttpClient');
        $this->assertInstanceOf(\Symfony\Component\HttpClient\MockHttpClient::class, $mockHttpClient);

        // 获取测试专用的 DifyApiClient 服务（使用 MockHttpClient）
        $apiClient = self::getServiceById('Test.DifyApiClient');
        $this->assertInstanceOf(DifyApiClient::class, $apiClient);

        // 设置 MockHttpClient 的响应
        $mockHttpClient->setResponseFactory(function (string $method, string $url, array $options) {
            // 验证请求基本参数
            $this->assertEquals('GET', $method);
            $this->assertEquals('https://test.com/test', $url);
            $this->assertArrayHasKey('headers', $options);
            $this->assertIsArray($options['headers']);

            // 返回 Mock 响应
            return new \Symfony\Component\HttpClient\Response\MockResponse('', [
                'http_code' => 200,
            ]);
        });

        // 创建请求对象
        $request = $this->createMock(RequestInterface::class);
        $request->method('getRequestPath')->willReturn('/test');
        $request->method('getRequestMethod')->willReturn('GET');
        $request->method('getRequestOptions')->willReturn([]);

        // 设置应用并执行请求
        $apiClient->setApp($app);
        $result = $apiClient->request($request);

        // 验证返回的是 ResponseInterface 实例
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    public function testSetAppThrowsExceptionWhenAppInvalid(): void
    {
        $app = new DifyApp();
        $app->setName('Invalid App');
        $app->setValid(false);

        $this->expectException(InvalidDifyConfigException::class);
        $this->expectExceptionMessage("Dify应用 'Invalid App' 已被禁用");

        $this->apiClient->setApp($app);
    }
}
