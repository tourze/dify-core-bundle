<?php

declare(strict_types=1);

namespace Tourze\DifyCoreBundle\Tests;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\DifyCoreBundle\DifyCoreBundle;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;

/**
 * DifyCoreBundle 基础测试类
 * @internal
 */
#[CoversClass(DifyCoreBundle::class)]
#[RunTestsInSeparateProcesses]
final class DifyCoreBundleTest extends AbstractBundleTestCase
{
    #[Test]
    public function testBundleIsInstanceOfBundle(): void
    {
        $bundleClass = self::getBundleClass();
        $bundle = new $bundleClass();
        $this->assertInstanceOf(Bundle::class, $bundle);
    }

    #[Test]
    public function testGetPath(): void
    {
        $bundleClass = self::getBundleClass();
        $bundle = new $bundleClass();
        $this->assertInstanceOf(DifyCoreBundle::class, $bundle);
        $expectedPath = \dirname(__DIR__) . '/src';
        $this->assertEquals($expectedPath, $bundle->getPath());
    }

    #[Test]
    public function testGetBundleDependencies(): void
    {
        $dependencies = DifyCoreBundle::getBundleDependencies();

        $this->assertIsArray($dependencies);
        $this->assertArrayHasKey(DoctrineBundle::class, $dependencies);
        $this->assertEquals(['all' => true], $dependencies[DoctrineBundle::class]);
    }

    #[Test]
    public function testBundleDependenciesStructure(): void
    {
        $dependencies = DifyCoreBundle::getBundleDependencies();

        foreach ($dependencies as $bundleClass => $config) {
            $this->assertIsString($bundleClass);
            $this->assertIsArray($config);
            $this->assertArrayHasKey('all', $config);
            $this->assertIsBool($config['all']);
        }
    }

    #[Test]
    public function testBundleNamespace(): void
    {
        $bundleClass = self::getBundleClass();
        $bundle = new $bundleClass();
        $reflection = new \ReflectionClass($bundle);
        $this->assertEquals('Tourze\DifyCoreBundle', $reflection->getNamespaceName());
    }

    #[Test]
    public function testBundleIsFinal(): void
    {
        $bundleClass = self::getBundleClass();
        /** @var class-string $bundleClass */
        $reflection = new \ReflectionClass($bundleClass);
        $this->assertTrue($reflection->isFinal());
    }
}
