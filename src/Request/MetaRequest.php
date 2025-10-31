<?php

declare(strict_types=1);

namespace Tourze\DifyCoreBundle\Request;

use HttpClientBundle\Request\ApiRequest;

/**
 * 获取应用 Meta 信息请求
 */
final class MetaRequest extends ApiRequest
{
    public function getRequestPath(): string
    {
        return '/meta';
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
