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
}
