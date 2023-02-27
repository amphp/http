<?php declare(strict_types=1);

namespace Amp\Http;

use PHPUnit\Framework\TestCase;

class TestHttpResponse extends HttpResponse
{
    public function setStatus(int $status, ?string $reason = null): void
    {
        parent::setStatus($status, $reason);
    }
}

class HttpResponseTest extends TestCase
{
    public function testStatusCodeOutOfRangeBelow(): void
    {
        $this->expectException(\Error::class);
        new TestHttpResponse(99);
    }

    public function testStatusCodeOutOfRangeAbove(): void
    {
        $this->expectException(\Error::class);
        new TestHttpResponse(600);
    }

    public function testSetStatus(): void
    {
        $response = new TestHttpResponse(200);
        self::assertSame(200, $response->getStatus());
        self::assertSame(HttpStatus::getReason(200), $response->getReason());

        $reason = 'Custom status reason';
        $response->setStatus(480, $reason);
        self::assertSame(480, $response->getStatus());
        self::assertSame($reason, $response->getReason());
    }

    public function provideStatuses(): iterable
    {
        yield 'informational' => [100, true, false, false, false, false];
        yield 'success' => [200, false, true, false, false, false];
        yield 'redirect' => [300, false, false, true, false, false];
        yield 'client-error' => [400, false, false, false, true, false];
        yield 'server-error' => [500, false, false, false, false, true];
    }

    /**
     * @dataProvider provideStatuses
     */
    public function testStatusMethods(
        int $code,
        bool $info,
        bool $success,
        bool $redirect,
        bool $clientError,
        bool $serverError
    ): void {
        $response = new TestHttpResponse($code);

        self::assertSame($info, $response->isInformational());
        self::assertSame($success, $response->isSuccessful());
        self::assertSame($redirect, $response->isRedirect());
        self::assertSame($clientError, $response->isClientError());
        self::assertSame($serverError, $response->isServerError());
    }
}
