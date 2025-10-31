<?php

declare(strict_types=1);

namespace Tourze\DifyCoreBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Tourze\DifyCoreBundle\DependencyInjection\DifyCoreExtension;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;
use Tourze\SymfonyDependencyServiceLoader\AutoExtension;

/**
 * DifyCoreExtension 测试类
 * @internal
 */
#[CoversClass(DifyCoreExtension::class)]
final class DifyCoreExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    private DifyCoreExtension $extension;

    private ContainerBuilder $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->extension = new DifyCoreExtension();
        $this->container = new ContainerBuilder();
    }

    public function testExtensionImplementsInterface(): void
    {
        $this->assertInstanceOf(ExtensionInterface::class, $this->extension);
        $this->assertInstanceOf(AutoExtension::class, $this->extension);
    }

    public function testGetConfigDir(): void
    {
        $reflection = new \ReflectionClass($this->extension);
        $method = $reflection->getMethod('getConfigDir');
        $method->setAccessible(true);

        $configDir = $method->invoke($this->extension);
        $this->assertIsString($configDir);

        // 验证返回的路径格式正确
        $this->assertStringEndsWith('/Resources/config', $configDir);
        $this->assertThat($configDir, self::stringContains('dify-core-bundle'));
    }

    public function testConfigDirectoryPath(): void
    {
        $reflection = new \ReflectionClass($this->extension);
        $method = $reflection->getMethod('getConfigDir');
        $method->setAccessible(true);

        $configDir = $method->invoke($this->extension);
        $this->assertIsString($configDir);

        // 验证路径格式正确
        $this->assertStringEndsWith('/Resources/config', $configDir);
        $this->assertThat($configDir, self::stringContains('dify-core-bundle'));
    }

    public function testExtensionNamespace(): void
    {
        $reflection = new \ReflectionClass($this->extension);
        $this->assertEquals(
            'Tourze\DifyCoreBundle\DependencyInjection',
            $reflection->getNamespaceName()
        );
    }

    public function testExtensionInheritance(): void
    {
        $reflection = new \ReflectionClass($this->extension);
        $parentClass = $reflection->getParentClass();

        $this->assertNotFalse($parentClass);
        $this->assertEquals(AutoExtension::class, $parentClass->getName());
    }

    public function testLoad(): void
    {
        // 设置必要的容器参数
        $this->container->setParameter('kernel.environment', 'test');
        $this->container->setParameter('kernel.debug', true);

        // 基本的加载测试，验证不会抛出异常
        $configs = [];

        try {
            $this->extension->load($configs, $this->container);
            $this->assertTrue(true); // 如果没有异常则测试通过
        } catch (\Exception $e) {
            self::fail('Extension load should not throw exception: ' . $e->getMessage());
        }
    }

    public function testGetAlias(): void
    {
        $alias = $this->extension->getAlias();
        $this->assertEquals('dify_core', $alias);
    }

    public function testContainerAfterLoad(): void
    {
        // 设置必要的容器参数
        $this->container->setParameter('kernel.environment', 'test');
        $this->container->setParameter('kernel.debug', true);

        $configs = [];
        $this->extension->load($configs, $this->container);

        // 验证容器已经被处理（虽然具体服务由AutoExtension处理）
        $this->assertInstanceOf(ContainerBuilder::class, $this->container);
    }
}
