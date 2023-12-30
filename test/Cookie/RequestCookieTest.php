<?php declare(strict_types=1);

namespace Amp\Http\Cookie;

use PHPUnit\Framework\TestCase;

class RequestCookieTest extends TestCase
{
    public function testParsing()
    {
        $this->assertEquals([new RequestCookie("foobar", "xxx")], RequestCookie::fromHeader("foobar=xxx"));
        $this->assertEquals([new RequestCookie("foobar", "x%20x")], RequestCookie::fromHeader("foobar=x%20x"));
        $this->assertEquals([new RequestCookie("a", "1"), new RequestCookie("b", "2")], RequestCookie::fromHeader("a=1;b=2"));
        $this->assertEquals([new RequestCookie("a", "1"), new RequestCookie("b", "2")], RequestCookie::fromHeader("a=1; b=2"));
        $this->assertEquals([new RequestCookie("a", "1"), new RequestCookie("b", "2")], RequestCookie::fromHeader("a=1 ;b=2"));
        $this->assertEquals([new RequestCookie("a", "1"), new RequestCookie("b", "-2")], RequestCookie::fromHeader("a=1; b = -2"));
        $this->assertEquals([new RequestCookie("a", "1"), new RequestCookie("b", "2,2")], RequestCookie::fromHeader("a=1; b=2,2"));
        $this->assertEquals([], RequestCookie::fromHeader("a=1; b=2 2"));
        $this->assertEquals([], RequestCookie::fromHeader("a=1; b"));
    }

    /**
     * Tests that when a zero-length phantom cookie is trailing due to the terminating semicolon, it is simply
     * discarded without impacting any other cookies present.
     */
    public function testZeroLengthTrailingCookie(): void
    {
        self::assertCount(1, $cookies = RequestCookie::fromHeader('a=1;'));
        self::assertEquals([new RequestCookie('a', '1')], $cookies);
    }

    /**
     * Tests that trailing whitespace is ignored.
     */
    public function testWhitespaceTrailingCookie(): void
    {
        self::assertCount(1, $cookies = RequestCookie::fromHeader('a=1; '));
        self::assertEquals([new RequestCookie('a', '1')], $cookies);
    }

    public function testInvalidCookieName()
    {
        $this->expectException(InvalidCookieException::class);

        new RequestCookie("foo bar");
    }

    public function testInvalidCookieNameModify()
    {
        $cookie = new RequestCookie("foobar");

        $this->expectException(InvalidCookieException::class);

        $cookie->withName('foo bar');
    }

    public function testInvalidCookieValue()
    {
        $this->expectException(InvalidCookieException::class);

        new RequestCookie("foobar", "what is this");
    }

    public function testInvalidCookieValueModify()
    {
        $cookie = new RequestCookie("foobar", "what-is-this");

        $this->expectException(InvalidCookieException::class);

        $cookie->withValue('what is this');
    }

    public function testGetters()
    {
        $cookie = new RequestCookie("foobar", "baz");

        $this->assertSame("foobar", $cookie->getName());
        $this->assertSame("baz", $cookie->getValue());
        $this->assertSame("foobar=baz", (string) $cookie);
    }

    public function testModifyName()
    {
        $cookie = new RequestCookie("foobar", "what-is-this");
        $newCookie = $cookie->withName('bar');

        $this->assertSame('foobar', $cookie->getName());
        $this->assertSame('bar', $newCookie->getName());
    }

    public function testModifyValue()
    {
        $cookie = new RequestCookie("foobar", "what-is-this");
        $newCookie = $cookie->withValue('what-is-that');

        $this->assertSame('what-is-this', $cookie->getValue());
        $this->assertSame('what-is-that', $newCookie->getValue());
    }
}
