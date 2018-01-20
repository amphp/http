<?php

namespace Amp\Http\Parser;

use PHPUnit\Framework\TestCase;

class Rfc7230Test extends TestCase {
    /** @dataProvider provideValidHeaders */
    public function testValidHeaderParsing(string $rawHeaders, array $expectedResult) {
        $result = Rfc7230::parseHeaders($rawHeaders);
        $this->assertSame($result, $expectedResult);
    }

    /** @dataProvider provideValidHeaders */
    public function testValidHeaderFormatting(string $rawHeaders /* ignored for this case */, array $expectedResult) {
        $result = Rfc7230::parseHeaders(Rfc7230::formatHeaders($expectedResult));
        $this->assertSame($result, $expectedResult);
    }

    public function provideValidHeaders() {
        return [
            ["x:y\r\n", ["x" => ["y"]]],
            ["server:\tamphp.org\r\n", ["server" => ["amphp.org"]]],
            ["server: \tamphp.org  \t \r\n", ["server" => ["amphp.org"]]],
            ["server: \tamphp.org  \t \r\nServer: amphp.org\r\n", ["server" => ["amphp.org", "amphp.org"]]],
            ["ser124ver:\tamphp.org\r\n", ["ser124ver" => ["amphp.org"]]],
        ];
    }

    /** @dataProvider provideInvalidHeaders */
    public function testInvalidHeaderParsing(string $rawHeaders) {
        $this->expectException(ParseException::class);
        Rfc7230::parseHeaders($rawHeaders);
    }

    public function provideInvalidHeaders() {
        return [
            [" x:y\r\n"],
            ["x :z\r\n"],
            [" x:z\r\n"],
            ["x :z\nfoo: bar\r\n"],
            ["x:z\nfoo: bar\r\n"],
            ["ser ver:\tamphp.org\r\n"],
            ["server:\tamphp.org\r\n fold\r\n"],
        ];
    }
}
