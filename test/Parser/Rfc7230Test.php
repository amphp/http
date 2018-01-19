<?php

namespace Amp\Http\Parser;

use PHPUnit\Framework\TestCase;

class Rfc7230Test extends TestCase {
    /** @dataProvider provideValidHeaders */
    public function testValidHeaders(string $rawHeaders, array $expectedResult) {
        $result = Rfc7230::parseHeaders($rawHeaders);
        $this->assertSame($result, $expectedResult);
    }

    public function provideValidHeaders() {
        return [
            ["x:y\r\n", ["x" => ["y"]]],
            ["x:z\n", ["x" => ["z"]]],
            ["server:\tamphp.org\r\n", ["server" => ["amphp.org"]]],
            ["server: \tamphp.org  \t \r\n", ["server" => ["amphp.org"]]],
            ["server: \tamphp.org  \t \r\nServer: amphp.org\n", ["server" => ["amphp.org", "amphp.org"]]],
            ["ser124ver:\tamphp.org\r\n", ["ser124ver" => ["amphp.org"]]],
        ];
    }

    /** @dataProvider provideInvalidHeaders */
    public function testInvalidHeaders(string $rawHeaders) {
        $this->expectException(ParseException::class);
        Rfc7230::parseHeaders($rawHeaders);
    }

    public function provideInvalidHeaders() {
        return [
            [" x:y\r\n"],
            ["x :z\n"],
            ["ser ver:\tamphp.org\r\n"],
            ["server:\tamphp.org\r\n fold\r\n"],
        ];
    }
}