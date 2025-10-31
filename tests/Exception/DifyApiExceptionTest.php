<?php

declare(strict_types=1);

namespace Tourze\DifyCoreBundle\Tests\Exception;

use HttpClientBundle\Exception\HttpClientException;
use HttpClientBundle\Request\RequestInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Tourze\DifyCoreBundle\Exception\DifyApiException;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * DifyApiException 测试类
 * @internal
 */
#[CoversClass(DifyApiException::class)]
final class DifyApiExceptionTest extends AbstractExceptionTestCase
{
    public function testExtendsHttpClientException(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(400);

        $exception = new DifyApiException($request, $response);

        $this->assertInstanceOf(HttpClientException::class, $exception);
    }

    public function testConstructorWithMessage(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(400);

        $message = 'Test error message';
        $exception = new DifyApiException($request, $response, $message);

        $this->assertEquals($message, $exception->getMessage());
    }

    public function testConstructorWithErrorCode(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(400);

        $errorCode = 'INVALID_REQUEST';
        $exception = new DifyApiException($request, $response, 'Test message', $errorCode);

        $this->assertEquals($errorCode, $exception->getErrorCode());
    }

    public function testConstructorWithNullErrorCode(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(400);

        $exception = new DifyApiException($request, $response, 'Test message', null);

        $this->assertNull($exception->getErrorCode());
    }

    public function testConstructorWithPreviousException(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(400);

        $previous = new \Exception('Previous exception');
        $exception = new DifyApiException($request, $response, 'Test message', null, $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testGetCode(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(400);

        $exception = new DifyApiException($request, $response);

        $this->assertEquals(400, $exception->getCode());
    }

    public function testDefaultMessage(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(500);

        $exception = new DifyApiException($request, $response);

        $this->assertEquals('', $exception->getMessage());
    }
}
