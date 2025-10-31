<?php

declare(strict_types=1);

namespace Tourze\DifyCoreBundle\Exception;

use HttpClientBundle\Exception\HttpClientException;
use HttpClientBundle\Request\RequestInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Dify API 异常
 */
final class DifyApiException extends HttpClientException
{
    public function __construct(
        private readonly RequestInterface $request,
        private readonly ResponseInterface $response,
        string $message = '',
        private readonly ?string $errorCode = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($request, $response, $message, $response->getStatusCode(), $previous);

        // 添加自定义的errorCode到context中
        if (null !== $errorCode) {
            $context = $this->getContext();
            $context['errorCode'] = $errorCode;
            $this->setContext($context);
        }
    }

    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
