<?php

namespace Amp\Http;

use PHPUnit\Framework\TestCase;

class ParseTokenListHeaderTest extends TestCase
{
    public function test()
    {
        self::assertSame([
            'no-cache' => '',
            'no-store' => '',
            'must-revalidate' => '',
        ], parseTokenListHeader($this->createMessage(['cache-control' => 'no-cache, no-store, must-revalidate']), 'cache-control'));

        self::assertSame([
            'public' => '',
            'max-age' => '31536000',
        ], parseTokenListHeader($this->createMessage(['cache-control' => 'public, max-age=31536000']), 'cache-control'));

        self::assertSame([
            'private' => 'foo, bar',
            'max-age' => '31536000',
        ], parseTokenListHeader($this->createMessage(['cache-control' => 'private="foo, bar", max-age=31536000']), 'cache-control'));

        self::assertNull(parseTokenListHeader($this->createMessage(['cache-control' => 'private="foo, bar, max-age=31536000']), 'cache-control'));

        self::assertSame([
            'private' => 'foo"bar',
            'max-age' => '31536000',
        ], parseTokenListHeader($this->createMessage(['cache-control' => 'private="foo\"bar", max-age=31536000']), 'cache-control'));

        self::assertSame([
            'private' => 'foo""bar',
            'max-age' => '31536000',
        ], parseTokenListHeader($this->createMessage(['cache-control' => 'private="foo\"\"bar", max-age=31536000']), 'cache-control'));

        self::assertSame([
            'private' => 'foo\\',
            'bar' => '',
        ], parseTokenListHeader($this->createMessage(['cache-control' => 'private="foo\\\\", bar']), 'cache-control'));
    }

    private function createMessage(array $headers): Message
    {
        return new class($headers) extends Message {
            public function __construct(array $headers)
            {
                $this->setHeaders($headers);
            }
        };
    }
}
