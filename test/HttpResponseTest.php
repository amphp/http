<?php

namespace Amp\Http;

use PHPUnit\Framework\TestCase;

class TestHttpResponse extends HttpResponse
{
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
}
