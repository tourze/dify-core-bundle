<?php

declare(strict_types=1);

namespace Tourze\DifyCoreBundle\Tests\Service;

use HttpClientBundle\Client\ApiClient;
use HttpClientBundle\Request\RequestInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Tourze\DifyCoreBundle\Entity\DifyApp;
use Tourze\DifyCoreBundle\Exception\DifyApiException;
use Tourze\DifyCoreBundle\Exception\InvalidDifyConfigException;
use Tourze\DifyCoreBundle\Service\DifyApiClient;
use Tourze\DoctrineAsyncInsertBundle\Service\AsyncInsertService;

/**
 * DifyApiClient 测试类
 * @internal
 * @phpstan-ignore-next-line tourze.serviceTestShouldExtendIntegrationTestCase
 */
#[CoversClass(DifyApiClient::class)]
final class DifyApiClientTest extends TestCase
{
    private DifyApiClient $apiClient;

    private HttpClientInterface&MockObject $httpClient;

    private AsyncInsertService&MockObject $asyncInsertService;

    private EventDispatcherInterface&MockObject $eventDispatcher;

    private CacheInterface&MockObject $cache;

    private LockFactory&MockObject $lockFactory;

    private LoggerInterface&MockObject $logger;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->asyncInsertService = $this->createMock(AsyncInsertService::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->cache = $this->createMock(CacheInterface::class);
        $this->lockFactory = $this->createMock(LockFactory::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->apiClient = new DifyApiClient(
            $this->httpClient,
            $this->asyncInsertService,
            $this->eventDispatcher,
            $this->cache,
            $this->lockFactory,
            $this->logger
        );
    }

    public function testExtendsApiClient(): void
    {
        $this->assertInstanceOf(ApiClient::class, $this->apiClient);
    }

    public function testHasCorrectMonologChannelAttribute(): void
    {
        $reflection = new \ReflectionClass($this->apiClient);
        $attributes = $reflection->getAttributes();

        $hasMonologChannel = false;
        foreach ($attributes as $attribute) {
            if (str_contains($attribute->getName(), 'WithMonologChannel')) {
                $arguments = $attribute->getArguments();
                if (isset($arguments['channel']) && 'dify_api' === $arguments['channel']) {
                    $hasMonologChannel = true;
                    break;
                }
            }
        }

        $this->assertTrue($hasMonologChannel, 'Class should have WithMonologChannel attribute with channel "dify_api"');
    }

    public function testSetAppWithValidApp(): void
    {
        $app = $this->createValidDifyApp();

        $this->apiClient->setApp($app);

        // 验证没有抛出异常
        $this->assertTrue(true);
    }

    public function testSetAppWithInvalidAppThrowsException(): void
    {
        $app = $this->createMock(DifyApp::class);
        $app->method('isValid')->willReturn(false);
        $app->method('getName')->willReturn('Invalid App');

        $this->expectException(InvalidDifyConfigException::class);
        $this->expectExceptionMessage("Dify应用 'Invalid App' 已被禁用");

        $this->apiClient->setApp($app);
    }

    public function testRequestWithoutAppThrowsException(): void
    {
        $request = $this->createMock(RequestInterface::class);

        $this->expectException(InvalidDifyConfigException::class);
        $this->expectExceptionMessage('请先通过 setApp() 设置 Dify 应用');

        $this->apiClient->request($request);
    }

    public function testRequestWithValidApp(): void
    {
        $app = $this->createValidDifyApp();
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $request->method('getRequestPath')->willReturn('/test');
        $request->method('getRequestMethod')->willReturn('GET');
        $request->method('getRequestOptions')->willReturn([]);

        $response->method('getStatusCode')->willReturn(200);

        // 创建一个部分mock来测试request方法
        $partialMock = $this->getMockBuilder(DifyApiClient::class)
            ->setConstructorArgs([
                $this->httpClient,
                $this->asyncInsertService,
                $this->eventDispatcher,
                $this->cache,
                $this->lockFactory,
                $this->logger,
            ])
            ->onlyMethods(['getHttpClient', 'getRequestUrl', 'getRequestMethod', 'getRequestOptions', 'formatResponse'])
            ->getMock()
        ;

        $partialMock->method('getHttpClient')->willReturn($this->httpClient);
        $partialMock->method('getRequestUrl')->willReturn('https://api.dify.ai/test');
        $partialMock->method('getRequestMethod')->willReturn('GET');
        $partialMock->method('getRequestOptions')->willReturn([]);
        $partialMock->method('formatResponse')->willReturn($response);

        $this->httpClient
            ->method('request')
            ->willReturn($response)
        ;

        $partialMock->setApp($app);
        $result = $partialMock->request($request);

        $this->assertSame($response, $result);
    }

    public function testGetLoggerWithProvidedLogger(): void
    {
        $reflection = new \ReflectionClass($this->apiClient);
        $method = $reflection->getMethod('getLogger');
        $method->setAccessible(true);

        $result = $method->invoke($this->apiClient);

        $this->assertSame($this->logger, $result);
    }

    public function testGetLoggerWithoutProvidedLogger(): void
    {
        $apiClient = new DifyApiClient(
            $this->createMock(HttpClientInterface::class),
            $this->createMock(AsyncInsertService::class),
            $this->createMock(EventDispatcherInterface::class),
            null,
            null,
            null
        );

        $reflection = new \ReflectionClass($apiClient);
        $method = $reflection->getMethod('getLogger');
        $method->setAccessible(true);

        $result = $method->invoke($apiClient);

        $this->assertInstanceOf(LoggerInterface::class, $result);
    }

    public function testGetHttpClient(): void
    {
        $reflection = new \ReflectionClass($this->apiClient);
        $method = $reflection->getMethod('getHttpClient');
        $method->setAccessible(true);

        $result = $method->invoke($this->apiClient);

        $this->assertSame($this->httpClient, $result);
    }

    public function testGetLockFactory(): void
    {
        $reflection = new \ReflectionClass($this->apiClient);
        $method = $reflection->getMethod('getLockFactory');
        $method->setAccessible(true);

        $result = $method->invoke($this->apiClient);

        $this->assertSame($this->lockFactory, $result);
    }

    public function testGetLockFactoryWithoutProvidedFactory(): void
    {
        $apiClient = new DifyApiClient(
            $this->createMock(HttpClientInterface::class),
            $this->createMock(AsyncInsertService::class),
            $this->createMock(EventDispatcherInterface::class),
            null,
            null,
            null
        );

        $reflection = new \ReflectionClass($apiClient);
        $method = $reflection->getMethod('getLockFactory');
        $method->setAccessible(true);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('LockFactory not available');

        $method->invoke($apiClient);
    }

    public function testGetCache(): void
    {
        $reflection = new \ReflectionClass($this->apiClient);
        $method = $reflection->getMethod('getCache');
        $method->setAccessible(true);

        $result = $method->invoke($this->apiClient);

        $this->assertSame($this->cache, $result);
    }

    public function testGetCacheWithoutProvidedCache(): void
    {
        $apiClient = new DifyApiClient(
            $this->createMock(HttpClientInterface::class),
            $this->createMock(AsyncInsertService::class),
            $this->createMock(EventDispatcherInterface::class),
            null,
            null,
            null
        );

        $reflection = new \ReflectionClass($apiClient);
        $method = $reflection->getMethod('getCache');
        $method->setAccessible(true);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cache not available');

        $method->invoke($apiClient);
    }

    public function testGetEventDispatcher(): void
    {
        $reflection = new \ReflectionClass($this->apiClient);
        $method = $reflection->getMethod('getEventDispatcher');
        $method->setAccessible(true);

        $result = $method->invoke($this->apiClient);

        $this->assertSame($this->eventDispatcher, $result);
    }

    public function testGetAsyncInsertService(): void
    {
        $reflection = new \ReflectionClass($this->apiClient);
        $method = $reflection->getMethod('getAsyncInsertService');
        $method->setAccessible(true);

        $result = $method->invoke($this->apiClient);

        $this->assertSame($this->asyncInsertService, $result);
    }

    public function testGetRequestUrlWithoutApp(): void
    {
        $request = $this->createMock(RequestInterface::class);

        $reflection = new \ReflectionClass($this->apiClient);
        $method = $reflection->getMethod('getRequestUrl');
        $method->setAccessible(true);

        $this->expectException(InvalidDifyConfigException::class);
        $this->expectExceptionMessage('未指定Dify应用');

        $method->invoke($this->apiClient, $request);
    }

    public function testGetRequestUrlWithApp(): void
    {
        $app = $this->createValidDifyApp();
        $app->method('getBaseUrl')->willReturn('https://api.dify.ai');

        $request = $this->createMock(RequestInterface::class);
        $request->method('getRequestPath')->willReturn('/test');

        $this->apiClient->setApp($app);

        $reflection = new \ReflectionClass($this->apiClient);
        $method = $reflection->getMethod('getRequestUrl');
        $method->setAccessible(true);

        $result = $method->invoke($this->apiClient, $request);

        $this->assertEquals('https://api.dify.ai/test', $result);
    }

    public function testGetRequestMethod(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $request->method('getRequestMethod')->willReturn('POST');

        $reflection = new \ReflectionClass($this->apiClient);
        $method = $reflection->getMethod('getRequestMethod');
        $method->setAccessible(true);

        $result = $method->invoke($this->apiClient, $request);

        $this->assertEquals('POST', $result);
    }

    public function testGetRequestMethodDefaultToGet(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $request->method('getRequestMethod')->willReturn(null);

        $reflection = new \ReflectionClass($this->apiClient);
        $method = $reflection->getMethod('getRequestMethod');
        $method->setAccessible(true);

        $result = $method->invoke($this->apiClient, $request);

        $this->assertEquals('GET', $result);
    }

    public function testGetRequestOptionsWithoutApp(): void
    {
        $request = $this->createMock(RequestInterface::class);

        $reflection = new \ReflectionClass($this->apiClient);
        $method = $reflection->getMethod('getRequestOptions');
        $method->setAccessible(true);

        $this->expectException(InvalidDifyConfigException::class);
        $this->expectExceptionMessage('未指定Dify应用');

        $method->invoke($this->apiClient, $request);
    }

    public function testGetRequestOptionsWithApp(): void
    {
        $app = $this->createValidDifyApp();
        $app->method('getApiKey')->willReturn('test-api-key');

        $request = $this->createMock(RequestInterface::class);
        $request->method('getRequestOptions')->willReturn([
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);

        $this->apiClient->setApp($app);

        $reflection = new \ReflectionClass($this->apiClient);
        $method = $reflection->getMethod('getRequestOptions');
        $method->setAccessible(true);

        $result = $method->invoke($this->apiClient, $request);

        $expectedOptions = [
            'headers' => [
                'Authorization' => 'Bearer test-api-key',
                'Content-Type' => 'application/json',
            ],
        ];

        $this->assertEquals($expectedOptions, $result);
    }

    public function testFormatResponseWithSuccessfulResponse(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $reflection = new \ReflectionClass($this->apiClient);
        $method = $reflection->getMethod('formatResponse');
        $method->setAccessible(true);

        $result = $method->invoke($this->apiClient, $request, $response);

        $this->assertSame($response, $result);
    }

    public function testFormatResponseWithErrorResponse(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(400);
        $response->method('toArray')->willReturn([
            'message' => 'Bad Request',
            'code' => 'INVALID_REQUEST',
        ]);

        $reflection = new \ReflectionClass($this->apiClient);
        $method = $reflection->getMethod('formatResponse');
        $method->setAccessible(true);

        $this->expectException(DifyApiException::class);

        $method->invoke($this->apiClient, $request, $response);
    }

    public function testSetAppReturnType(): void
    {
        $reflection = new \ReflectionClass($this->apiClient);
        $method = $reflection->getMethod('setApp');
        $returnType = $method->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertEquals('void', $this->getTypeName($returnType));
    }

    private function createValidDifyApp(): DifyApp&MockObject
    {
        $app = $this->createMock(DifyApp::class);
        $app->method('isValid')->willReturn(true);
        $app->method('getName')->willReturn('Test App');

        return $app;
    }

    /**
     * PHP 8.4 兼容的类型名称获取方法
     */
    private function getTypeName(\ReflectionType $type): string
    {
        if ($type instanceof \ReflectionNamedType) {
            return $type->getName();
        }

        if ($type instanceof \ReflectionUnionType) {
            return implode('|', array_map(fn ($t) => $this->getTypeName($t), $type->getTypes()));
        }

        if ($type instanceof \ReflectionIntersectionType) {
            return implode('&', array_map(fn ($t) => $this->getTypeName($t), $type->getTypes()));
        }

        // Fallback for other types
        return (string) $type;
    }
}
