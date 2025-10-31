<?php

declare(strict_types=1);

namespace Tourze\DifyCoreBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\DifyCoreBundle\Exception\InvalidDifyConfigException;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * InvalidDifyConfigException 测试类
 * @internal
 */
#[CoversClass(InvalidDifyConfigException::class)]
final class InvalidDifyConfigExceptionTest extends AbstractExceptionTestCase
{
    public function testExtendsException(): void
    {
        $exception = new InvalidDifyConfigException();

        $this->assertInstanceOf(\Exception::class, $exception);
    }

    public function testConstructorWithMessage(): void
    {
        $message = 'Invalid Dify configuration';
        $exception = new InvalidDifyConfigException($message);

        $this->assertEquals($message, $exception->getMessage());
    }

    public function testConstructorWithCode(): void
    {
        $code = 400;
        $exception = new InvalidDifyConfigException('Test message', $code);

        $this->assertEquals($code, $exception->getCode());
    }

    public function testConstructorWithPreviousException(): void
    {
        $previous = new \Exception('Previous exception');
        $exception = new InvalidDifyConfigException('Test message', 0, $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testDefaultConstructor(): void
    {
        $exception = new InvalidDifyConfigException();

        $this->assertEquals('', $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testExceptionIsThrowable(): void
    {
        $exception = new InvalidDifyConfigException('Test message');

        $this->assertInstanceOf(\Throwable::class, $exception);
    }
}
