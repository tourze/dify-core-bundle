<?php

declare(strict_types=1);

namespace Tourze\DifyCoreBundle\Tests\Request;

use HttpClientBundle\Request\ApiRequest;
use HttpClientBundle\Test\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\DifyCoreBundle\Request\InfoRequest;

/**
 * InfoRequest 测试类
 * @internal
 */
#[CoversClass(InfoRequest::class)]
final class InfoRequestTest extends RequestTestCase
{
    private InfoRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $this->request = new InfoRequest();
    }

    public function testExtendsApiRequest(): void
    {
        $this->assertInstanceOf(ApiRequest::class, $this->request);
    }

    public function testIsFinal(): void
    {
        $reflection = new \ReflectionClass($this->request);
        $this->assertTrue($reflection->isFinal());
    }

    public function testGetRequestPath(): void
    {
        $this->assertEquals('/info', $this->request->getRequestPath());
    }

    public function testGetRequestMethod(): void
    {
        $this->assertEquals('GET', $this->request->getRequestMethod());
    }

    public function testGetRequestOptions(): void
    {
        $options = $this->request->getRequestOptions();

        $expectedOptions = [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ];

        $this->assertEquals($expectedOptions, $options);
    }

    public function testGetRequestOptionsStructure(): void
    {
        $options = $this->request->getRequestOptions();

        $this->assertIsArray($options);
        $this->assertArrayHasKey('headers', $options);
        $this->assertIsArray($options['headers']);
        $this->assertArrayHasKey('Content-Type', $options['headers']);
        $this->assertEquals('application/json', $options['headers']['Content-Type']);
    }

    public function testRequestOptionsReturnType(): void
    {
        $reflection = new \ReflectionClass($this->request);
        $method = $reflection->getMethod('getRequestOptions');
        $returnType = $method->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertEquals('array', $this->getTypeName($returnType));
    }

    public function testRequestPathReturnType(): void
    {
        $reflection = new \ReflectionClass($this->request);
        $method = $reflection->getMethod('getRequestPath');
        $returnType = $method->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertEquals('string', $this->getTypeName($returnType));
    }

    public function testRequestMethodReturnType(): void
    {
        $reflection = new \ReflectionClass($this->request);
        $method = $reflection->getMethod('getRequestMethod');
        $returnType = $method->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertEquals('string', $this->getTypeName($returnType));
    }

    public function testNamespace(): void
    {
        $reflection = new \ReflectionClass($this->request);
        $this->assertEquals('Tourze\DifyCoreBundle\Request', $reflection->getNamespaceName());
    }

    public function testMethodsArePublic(): void
    {
        $reflection = new \ReflectionClass($this->request);

        $this->assertTrue($reflection->getMethod('getRequestPath')->isPublic());
        $this->assertTrue($reflection->getMethod('getRequestMethod')->isPublic());
        $this->assertTrue($reflection->getMethod('getRequestOptions')->isPublic());
    }

    public function testNoConstructorParameters(): void
    {
        $reflection = new \ReflectionClass($this->request);
        $constructor = $reflection->getConstructor();

        if (null !== $constructor) {
            $this->assertCount(0, $constructor->getParameters());
        } else {
            $this->assertTrue(true, 'No explicit constructor defined');
        }
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
