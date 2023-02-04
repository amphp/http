<?php declare(strict_types=1);

namespace Amp\Http;

use PHPUnit\Framework\TestCase;

class TestHttpMessage extends HttpMessage
{
    public function __construct(array $headers = [])
    {
        $this->replaceHeaders($headers);
    }

    public function setHeaders(array $headers): void
    {
        parent::setHeaders($headers);
    }

    public function replaceHeaders(array $headers): void
    {
        parent::replaceHeaders($headers);
    }

    public function setHeader(string $name, $value): void
    {
        parent::setHeader($name, $value);
    }

    public function addHeader(string $name, $value): void
    {
        parent::addHeader($name, $value);
    }

    public function removeHeader(string $name): void
    {
        parent::removeHeader($name);
    }
}

class HttpMessageTest extends TestCase
{
    public function testGetRawHeaders(): void
    {
        $message = new TestHttpMessage([
            'X-FooBar' => 'bar',
            'X-Replace' => 'none'
        ]);

        // Replaces existing casing
        $message->setHeader('x-rePlace', 'yes');

        // Gets appended to the existing bucket, not after x-replace
        $message->addHeader('x-fooBar', 'baz');

        // Gets appended at the end, because name doesn't exist yet
        $message->addHeader('x-again', 'hello');

        $this->assertSame([
            ['X-FooBar', 'bar'],
            ['x-fooBar', 'baz'],
            ['x-rePlace', 'yes'],
            ['x-again', 'hello']
        ], $message->getRawHeaders());
    }

    public function testGetHeader(): void
    {
        $message = new TestHttpMessage([
            'foo' => 'bar',
        ]);

        $this->assertTrue($message->hasHeader('foo'));
        $this->assertSame(['foo' => ['bar']], $message->getHeaders());
        $this->assertSame('bar', $message->getHeader('foo'));
        $this->assertSame('bar', $message->getHeader('FOO'));
        $this->assertSame('bar', $message->getHeader('FoO'));
        $this->assertNull($message->getHeader('bar'));

        $this->assertSame(['bar'], $message->getHeaderArray('foo'));
        $this->assertSame([], $message->getHeaderArray('bar'));
    }

    public function testAddHeader(): void
    {
        $message = new TestHttpMessage([
            'foo' => 'bar',
        ]);

        $this->assertSame(['bar'], $message->getHeaderArray('foo'));

        $message->addHeader('foo', 'bar');
        $this->assertSame(['bar', 'bar'], $message->getHeaderArray('foo'));

        $message->addHeader('bar', 'bar');
        $this->assertSame(['bar'], $message->getHeaderArray('bar'));

        $message->addHeader('bar', ['baz']);
        $this->assertSame(['bar', 'baz'], $message->getHeaderArray('bar'));

        $message->addHeader('bar', []);
        $this->assertSame(['bar', 'baz'], $message->getHeaderArray('bar'));
    }

    public function testSetAndReplaceHeaders(): void
    {
        $message = new TestHttpMessage([
            'foo' => 'bar',
        ]);

        $this->assertSame(['bar'], $message->getHeaderArray('foo'));

        $message->setHeader('foo', 'bar');
        $this->assertSame(['bar'], $message->getHeaderArray('foo'));

        $message->setHeader('bar', 'bar');
        $this->assertSame(['bar'], $message->getHeaderArray('bar'));

        $message->replaceHeaders(['bar' => [], 'baz' => ['baz']]);
        $this->assertSame(['bar'], $message->getHeaderArray('foo'));
        $this->assertFalse($message->hasHeader('bar'));
        $this->assertSame([], $message->getHeaderArray('bar'));
        $this->assertTrue($message->hasHeader('baz'));
        $this->assertSame(['baz'], $message->getHeaderArray('baz'));

        $message->setHeaders(['foo' => ['new']]);
        $this->assertSame(['new'], $message->getHeaderArray('foo'));
        $this->assertFalse($message->hasHeader('bar'));
        $this->assertSame([], $message->getHeaderArray('bar'));
        $this->assertFalse($message->hasHeader('baz'));
        $this->assertSame([], $message->getHeaderArray('baz'));

        $message->setHeader('bar', ['biz', 'baz']);
        $this->assertSame(['biz', 'baz'], $message->getHeaderArray('bar'));
        $this->assertSame('biz', $message->getHeader('bar'));
    }

    public function testInvalidName(): void
    {
        $this->expectException(\AssertionError::class);
        $this->expectExceptionMessage('Invalid header name');

        $message = new TestHttpMessage;
        $message->setHeader("te\0st", 'value');
    }

    public function testInvalidValue(): void
    {
        $this->expectException(\AssertionError::class);
        $this->expectExceptionMessage('Invalid header value');

        $message = new TestHttpMessage;
        $message->setHeader('foo', "te\0st");
    }
}
