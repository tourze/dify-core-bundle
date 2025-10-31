<?php

declare(strict_types=1);

namespace Tourze\DifyCoreBundle\Service;

use HttpClientBundle\Client\ApiClient;
use HttpClientBundle\Request\RequestInterface;
use Monolog\Attribute\WithMonologChannel;
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
use Tourze\DoctrineAsyncInsertBundle\Service\AsyncInsertService;

/**
 * Dify API 客户端
 *
 * 使用示例：
 * $this->difyApiClient->setApp($difyApp);
 * $response = $this->difyApiClient->request($request);
 */
#[WithMonologChannel(channel: 'dify_api')]
class DifyApiClient extends ApiClient
{
    private ?DifyApp $currentApp = null;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly AsyncInsertService $asyncInsertService,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ?CacheInterface $cache = null,
        private readonly ?LockFactory $lockFactory = null,
        private readonly ?LoggerInterface $logger = null,
    ) {
    }

    /**
     * 设置要使用的 Dify 应用
     */
    public function setApp(DifyApp $app): void
    {
        if (false === $app->isValid()) {
            throw new InvalidDifyConfigException("Dify应用 '{$app->getName()}' 已被禁用");
        }

        $this->currentApp = $app;
    }

    /**
     * 发送请求到 Dify API
     */
    public function request(RequestInterface $request): mixed
    {
        if (null === $this->currentApp) {
            throw new InvalidDifyConfigException('请先通过 setApp() 设置 Dify 应用');
        }

        return parent::request($request);
    }

    protected function getLogger(): LoggerInterface
    {
        return $this->logger ?? new NullLogger();
    }

    protected function getHttpClient(): HttpClientInterface
    {
        return $this->httpClient;
    }

    protected function getLockFactory(): LockFactory
    {
        return $this->lockFactory ?? throw new \RuntimeException('LockFactory not available');
    }

    protected function getCache(): CacheInterface
    {
        return $this->cache ?? throw new \RuntimeException('Cache not available');
    }

    protected function getEventDispatcher(): EventDispatcherInterface
    {
        return $this->eventDispatcher;
    }

    protected function getAsyncInsertService(): AsyncInsertService
    {
        return $this->asyncInsertService;
    }

    protected function getRequestUrl(RequestInterface $request): string
    {
        if (null === $this->currentApp) {
            throw new InvalidDifyConfigException('未指定Dify应用');
        }

        return $this->currentApp->getBaseUrl() . $request->getRequestPath();
    }

    protected function getRequestMethod(RequestInterface $request): string
    {
        return $request->getRequestMethod() ?? 'GET';
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function getRequestOptions(RequestInterface $request): ?array
    {
        if (null === $this->currentApp) {
            throw new InvalidDifyConfigException('未指定Dify应用');
        }

        $options = $request->getRequestOptions() ?? [];

        // 只添加认证头，其他选项（包括Content-Type）由Request类决定
        $authHeaders = [
            'Authorization' => 'Bearer ' . $this->currentApp->getApiKey(),
        ];

        // 合并认证头，但不覆盖Request类设置的headers
        $existingHeaders = $options['headers'] ?? [];
        if (!is_array($existingHeaders)) {
            $existingHeaders = [];
        }
        $options['headers'] = array_merge(
            $authHeaders,
            $existingHeaders
        );

        return $options;
    }

    protected function formatResponse(RequestInterface $request, ResponseInterface $response): mixed
    {
        $statusCode = $response->getStatusCode();

        if ($statusCode >= 400) {
            $content = $response->toArray(false);
            $message = $content['message'] ?? 'Dify API request failed';
            $code = $content['code'] ?? null;

            throw new DifyApiException($request, $response, $message, $code);
        }

        return $response;
    }
}
