<?php

namespace Amp\Http\Cookie;

use PHPUnit\Framework\TestCase;

class CookieAttributesTest extends TestCase {
    public function testMaxAge() {
        $attributes = CookieAttributes::default()->withMaxAge(10);
        $this->assertSame(10, $attributes->getMaxAge());
        $this->assertNull($attributes->getExpiry());

        $attributes = $attributes->withoutMaxAge();
        $this->assertNull($attributes->getMaxAge());
        $this->assertNull($attributes->getExpiry());
    }

    public function testExpiry() {
        $expiry = new \DateTimeImmutable("now+10s");

        $attributes = CookieAttributes::default()->withExpiry($expiry);
        $this->assertSame($expiry, $attributes->getExpiry());
        $this->assertNull($attributes->getMaxAge());

        $attributes = $attributes->withoutExpiry();
        $this->assertNull($attributes->getExpiry());
        $this->assertNull($attributes->getMaxAge());
    }

    public function testSecure() {
        $attributes = CookieAttributes::default();

        $this->assertFalse($attributes->isSecure());
        $this->assertTrue($attributes->withSecure()->isSecure());
        $this->assertFalse($attributes->withSecure()->withoutSecure()->isSecure());
    }

    public function testHttpOnly() {
        $attributes = CookieAttributes::default();

        $this->assertTrue($attributes->isHttpOnly());
        $this->assertFalse($attributes->withoutHttpOnly()->isHttpOnly());
        $this->assertTrue($attributes->withoutHttpOnly()->withHttpOnly()->isHttpOnly());
    }
}
