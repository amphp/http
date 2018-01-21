<?php

namespace Amp\Http\Cookie;

use PHPUnit\Framework\TestCase;

class CookieAttributesTest extends TestCase {
    public function testExpiryIsRemovable() {
        $attributes = CookieAttributes::default()
            ->withMaxAge(10);
        $this->assertGreaterThan(0, $attributes->getExpires());

        $attributes = $attributes->withoutExpiry();
        $this->assertSame(0, $attributes->getExpires());
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
