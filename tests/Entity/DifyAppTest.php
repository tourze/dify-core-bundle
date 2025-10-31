<?php

declare(strict_types=1);

namespace Tourze\DifyCoreBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use Tourze\DifyCoreBundle\Entity\DifyApp;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * DifyApp 实体测试类
 * @internal
 */
#[CoversClass(DifyApp::class)]
final class DifyAppTest extends AbstractEntityTestCase
{
    private DifyApp $difyApp;

    protected function setUp(): void
    {
        $this->difyApp = new DifyApp();
    }

    public function testImplementsStringable(): void
    {
        $this->assertInstanceOf(\Stringable::class, $this->difyApp);
    }

    public function testNameGetterAndSetter(): void
    {
        $name = 'Test Dify App';
        $this->difyApp->setName($name);

        $this->assertEquals($name, $this->difyApp->getName());
    }

    public function testDescriptionGetterAndSetter(): void
    {
        $description = 'This is a test description';
        $this->difyApp->setDescription($description);

        $this->assertEquals($description, $this->difyApp->getDescription());
    }

    public function testDescriptionCanBeNull(): void
    {
        $this->difyApp->setDescription(null);

        $this->assertNull($this->difyApp->getDescription());
    }

    public function testApiKeyGetterAndSetter(): void
    {
        $apiKey = 'dify_api_key_12345';
        $this->difyApp->setApiKey($apiKey);

        $this->assertEquals($apiKey, $this->difyApp->getApiKey());
    }

    public function testBaseUrlGetterAndSetter(): void
    {
        $baseUrl = 'https://api.dify.ai';
        $this->difyApp->setBaseUrl($baseUrl);

        $this->assertEquals($baseUrl, $this->difyApp->getBaseUrl());
    }

    public function testBaseUrlTrimsTrailingSlash(): void
    {
        $baseUrlWithSlash = 'https://api.dify.ai/';
        $expectedUrl = 'https://api.dify.ai';

        $this->difyApp->setBaseUrl($baseUrlWithSlash);
        $this->assertEquals($expectedUrl, $this->difyApp->getBaseUrl());
    }

    public function testBaseUrlTrimsMultipleTrailingSlashes(): void
    {
        $baseUrlWithSlashes = 'https://api.dify.ai///';
        $expectedUrl = 'https://api.dify.ai';

        $this->difyApp->setBaseUrl($baseUrlWithSlashes);
        $this->assertEquals($expectedUrl, $this->difyApp->getBaseUrl());
    }

    public function testIframeCodeGetterAndSetter(): void
    {
        $iframeCode = '<iframe src="https://example.com"></iframe>';
        $this->difyApp->setIframeCode($iframeCode);

        $this->assertEquals($iframeCode, $this->difyApp->getIframeCode());
    }

    public function testIframeCodeCanBeNull(): void
    {
        $this->difyApp->setIframeCode(null);

        $this->assertNull($this->difyApp->getIframeCode());
    }

    public function testValidGetterAndSetter(): void
    {
        $this->difyApp->setValid(false);

        $this->assertFalse($this->difyApp->isValid());
    }

    public function testValidDefaultValue(): void
    {
        $this->assertTrue($this->difyApp->isValid());
    }

    public function testValidCanBeNull(): void
    {
        $this->difyApp->setValid(null);

        $this->assertNull($this->difyApp->isValid());
    }

    public function testGetApiUrl(): void
    {
        $baseUrl = 'https://api.dify.ai';
        $endpoint = '/chat-messages';
        $expectedUrl = 'https://api.dify.ai/v1/chat-messages';

        $this->difyApp->setBaseUrl($baseUrl);
        $result = $this->difyApp->getApiUrl($endpoint);

        $this->assertEquals($expectedUrl, $result);
    }

    public function testGetApiUrlWithEndpointWithoutLeadingSlash(): void
    {
        $baseUrl = 'https://api.dify.ai';
        $endpoint = 'chat-messages';
        $expectedUrl = 'https://api.dify.ai/v1chat-messages';

        $this->difyApp->setBaseUrl($baseUrl);
        $result = $this->difyApp->getApiUrl($endpoint);

        $this->assertEquals($expectedUrl, $result);
    }

    public function testGetApiHeaders(): void
    {
        $apiKey = 'test_api_key_123';
        $this->difyApp->setApiKey($apiKey);

        $headers = $this->difyApp->getApiHeaders();

        $expectedHeaders = [
            'Authorization' => 'Bearer test_api_key_123',
            'Content-Type' => 'application/json',
        ];

        $this->assertEquals($expectedHeaders, $headers);
    }

    public function testGetApiHeadersStructure(): void
    {
        $this->difyApp->setApiKey('any_key');
        $headers = $this->difyApp->getApiHeaders();

        $this->assertIsArray($headers);
        $this->assertArrayHasKey('Authorization', $headers);
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertStringStartsWith('Bearer ', $headers['Authorization']);
        $this->assertEquals('application/json', $headers['Content-Type']);
    }

    public function testToString(): void
    {
        $name = 'My Dify App';
        $this->difyApp->setName($name);

        $this->assertEquals($name, (string) $this->difyApp);
    }

    public function testCompleteEntityConfiguration(): void
    {
        $name = 'Complete App';
        $description = 'A complete test application';
        $apiKey = 'complete_api_key';
        $baseUrl = 'https://complete.dify.ai';
        $iframeCode = '<iframe src="https://complete.example.com"></iframe>';
        $valid = true;

        $this->difyApp->setName($name);
        $this->difyApp->setDescription($description);
        $this->difyApp->setApiKey($apiKey);
        $this->difyApp->setBaseUrl($baseUrl);
        $this->difyApp->setIframeCode($iframeCode);
        $this->difyApp->setValid($valid);

        $this->assertEquals($name, $this->difyApp->getName());
        $this->assertEquals($description, $this->difyApp->getDescription());
        $this->assertEquals($apiKey, $this->difyApp->getApiKey());
        $this->assertEquals($baseUrl, $this->difyApp->getBaseUrl());
        $this->assertEquals($iframeCode, $this->difyApp->getIframeCode());
        $this->assertTrue($this->difyApp->isValid());
    }

    #[TestWith(['https://api.dify.ai', 'https://api.dify.ai'])]
    #[TestWith(['https://api.dify.ai/', 'https://api.dify.ai'])]
    #[TestWith(['https://api.dify.ai///', 'https://api.dify.ai'])]
    #[TestWith(['https://api.dify.ai/v1', 'https://api.dify.ai/v1'])]
    #[TestWith(['https://api.dify.ai/v1/', 'https://api.dify.ai/v1'])]
    public function testBaseUrlNormalization(string $input, string $expected): void
    {
        $this->difyApp->setBaseUrl($input);
        $this->assertEquals($expected, $this->difyApp->getBaseUrl());
    }

    protected function createEntity(): DifyApp
    {
        return new DifyApp();
    }

    /**
     * 提供属性及其样本值的 Data Provider
     */
    /**
     * @return iterable<array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            ['name', 'Test App Name'],
            ['description', 'Test Description'],
            ['apiKey', 'test_api_key_123'],
            ['baseUrl', 'https://api.dify.ai'],
            ['iframeCode', '<iframe src="https://example.com"></iframe>'],
            ['valid', true],
        ];
    }
}
