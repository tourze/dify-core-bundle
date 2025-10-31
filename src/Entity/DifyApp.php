<?php

declare(strict_types=1);

namespace Tourze\DifyCoreBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;

/**
 * Dify应用配置实体
 *
 * 存储Dify AI应用的连接配置、API密钥和相关设置
 * 支持多应用配置，通过isActive字段控制当前激活的应用
 */
#[ORM\Entity]
#[ORM\Table(name: 'dify_apps', options: ['comment' => 'Dify应用配置表'])]
class DifyApp implements \Stringable
{
    use SnowflakeKeyAware;
    use TimestampableAware;

    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, length: 255, unique: true, options: ['comment' => 'Dify应用名称，用于标识不同的应用实例'])]
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank(message: '应用名称不能为空')]
    private string $name;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '应用描述信息'])]
    #[Assert\Length(max: 1000, maxMessage: '描述长度不能超过1000字符')]
    private ?string $description = null;

    #[ORM\Column(type: Types::STRING, length: 500, options: ['comment' => 'Dify应用的API密钥，用于身份验证'])]
    #[Assert\Length(max: 500)]
    #[Assert\NotBlank(message: 'API密钥不能为空')]
    private string $apiKey;

    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => 'Dify API的基础URL地址'])]
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank(message: '基础URL不能为空')]
    #[Assert\Url(message: '请输入有效的URL地址')]
    private string $baseUrl;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => 'Dify聊天窗口的iframe嵌入代码'])]
    #[Assert\Length(max: 10000, maxMessage: 'iframe代码长度不能超过10000字符')]
    private ?string $iframeCode = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['default' => 1, 'comment' => '是否有效'])]
    #[Assert\Type(type: 'bool')]
    private ?bool $valid = true;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function setApiKey(string $apiKey): void
    {
        $this->apiKey = $apiKey;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function setBaseUrl(string $baseUrl): void
    {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public function getIframeCode(): ?string
    {
        return $this->iframeCode;
    }

    public function setIframeCode(?string $iframeCode): void
    {
        $this->iframeCode = $iframeCode;
    }

    public function isValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(?bool $valid): void
    {
        $this->valid = $valid;
    }

    /**
     * 获取完整的API请求URL
     */
    public function getApiUrl(string $endpoint): string
    {
        return $this->baseUrl . '/v1' . $endpoint;
    }

    /**
     * 获取API请求头
     *
     * @return array<string, string>
     */
    public function getApiHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * 实现 Stringable 接口
     */
    public function __toString(): string
    {
        return $this->name;
    }
}
