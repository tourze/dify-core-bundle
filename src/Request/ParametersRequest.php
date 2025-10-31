<?php

declare(strict_types=1);

namespace Tourze\DifyCoreBundle\Request;

use HttpClientBundle\Request\ApiRequest;

/**
 * 获取应用参数请求
 */
final class ParametersRequest extends ApiRequest
{
    public function getRequestPath(): string
    {
        return '/parameters';
    }

    public function getRequestMethod(): string
    {
        return 'GET';
    }

    /**
     * @return array<string, mixed>
     */
    public function getRequestOptions(): array
    {
        return [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ];
    }
}
