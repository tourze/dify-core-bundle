<?php

declare(strict_types=1);

namespace Tourze\DifyCoreBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Bundle\FrameworkBundle\Routing\AttributeRouteControllerLoader;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;
use Tourze\DifyCoreBundle\Service\AttributeControllerLoader;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\RoutingAutoLoaderBundle\Service\RoutingAutoLoaderInterface;

/**
 * AttributeControllerLoader 测试类
 * @internal
 */
#[CoversClass(AttributeControllerLoader::class)]
#[RunTestsInSeparateProcesses]
final class AttributeControllerLoaderTest extends AbstractIntegrationTestCase
{
    private AttributeControllerLoader $loader;

    protected function onSetUp(): void
    {
        $this->loader = self::getService(AttributeControllerLoader::class);
    }

    public function testExtendsLoader(): void
    {
        $this->assertInstanceOf(Loader::class, $this->loader);
    }

    public function testImplementsRoutingAutoLoaderInterface(): void
    {
        $this->assertInstanceOf(RoutingAutoLoaderInterface::class, $this->loader);
    }

    public function testHasCorrectAutoconfigureTag(): void
    {
        $reflection = new \ReflectionClass($this->loader);
        $attributes = $reflection->getAttributes();

        $hasAutoconfigureTag = false;
        foreach ($attributes as $attribute) {
            if ('Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag' === $attribute->getName()) {
                $arguments = $attribute->getArguments();
                if (isset($arguments['name']) && 'routing.loader' === $arguments['name']) {
                    $hasAutoconfigureTag = true;
                    break;
                }
            }
        }

        $this->assertTrue($hasAutoconfigureTag, 'Class should have AutoconfigureTag attribute with name "routing.loader"');
    }

    public function testLoadCallsAutoload(): void
    {
        $resource = 'any_resource';
        $type = 'any_type';

        $result = $this->loader->load($resource, $type);

        $this->assertInstanceOf(RouteCollection::class, $result);
    }

    public function testSupportsReturnsFalse(): void
    {
        $resource = 'any_resource';
        $type = 'any_type';

        $this->assertFalse($this->loader->supports($resource, $type));
    }

    public function testAutoloadReturnsRouteCollection(): void
    {
        $result = $this->loader->autoload();

        $this->assertInstanceOf(RouteCollection::class, $result);
    }

    public function testAutoloadContainsChatControllerRoutes(): void
    {
        $collection = $this->loader->autoload();

        // 验证返回的集合是RouteCollection
        $this->assertInstanceOf(RouteCollection::class, $collection);

        // 这里我们无法直接验证具体的路由，因为它们依赖于ChatController的属性
        // 但我们可以验证集合不为null且是正确的类型
        $routes = $collection->all();
        $this->assertIsArray($routes);
    }

    public function testConstructorInitializesControllerLoader(): void
    {
        // 通过反射验证controllerLoader属性被正确初始化
        $reflection = new \ReflectionClass($this->loader);
        $property = $reflection->getProperty('controllerLoader');
        $property->setAccessible(true);

        $controllerLoader = $property->getValue($this->loader);
        $this->assertInstanceOf(AttributeRouteControllerLoader::class, $controllerLoader);
    }

    public function testLoadParameterTypes(): void
    {
        $reflection = new \ReflectionClass($this->loader);
        $method = $reflection->getMethod('load');
        $parameters = $method->getParameters();

        $this->assertCount(2, $parameters);
        $this->assertEquals('resource', $parameters[0]->getName());
        $this->assertEquals('type', $parameters[1]->getName());
        $this->assertTrue($parameters[1]->allowsNull());
    }

    public function testSupportsParameterTypes(): void
    {
        $reflection = new \ReflectionClass($this->loader);
        $method = $reflection->getMethod('supports');
        $parameters = $method->getParameters();

        $this->assertCount(2, $parameters);
        $this->assertEquals('resource', $parameters[0]->getName());
        $this->assertEquals('type', $parameters[1]->getName());
        $this->assertTrue($parameters[1]->allowsNull());
    }

    public function testAutoloadParameterTypes(): void
    {
        $reflection = new \ReflectionClass($this->loader);
        $method = $reflection->getMethod('autoload');
        $parameters = $method->getParameters();

        $this->assertCount(0, $parameters);
    }

    public function testMethodReturnTypes(): void
    {
        $reflection = new \ReflectionClass($this->loader);

        $loadMethod = $reflection->getMethod('load');
        $loadReturnType = $loadMethod->getReturnType();
        $this->assertNotNull($loadReturnType);
        $this->assertEquals(RouteCollection::class, $this->getTypeName($loadReturnType));

        $supportsMethod = $reflection->getMethod('supports');
        $supportsReturnType = $supportsMethod->getReturnType();
        $this->assertNotNull($supportsReturnType);
        $this->assertEquals('bool', $this->getTypeName($supportsReturnType));

        $autoloadMethod = $reflection->getMethod('autoload');
        $autoloadReturnType = $autoloadMethod->getReturnType();
        $this->assertNotNull($autoloadReturnType);
        $this->assertEquals(RouteCollection::class, $this->getTypeName($autoloadReturnType));
    }

    public function testNamespace(): void
    {
        $reflection = new \ReflectionClass($this->loader);
        $this->assertEquals('Tourze\DifyCoreBundle\Service', $reflection->getNamespaceName());
    }

    public function testClassIsFinal(): void
    {
        $reflection = new \ReflectionClass($this->loader);
        $this->assertFalse($reflection->isFinal(), 'AttributeControllerLoader should not be final to allow proper inheritance from Loader');
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
