<?php declare(strict_types=1);

namespace Amp\Http\Http1;

use Amp\Http\InvalidHeaderException;
use PHPUnit\Framework\TestCase;
use function Amp\Http\convertHeaderPairsToMap;

class Rfc7230Test extends TestCase
{
    /** @dataProvider provideValidHeaders */
    public function testValidHeaderPairParsing(string $rawHeaders, array $expectedResult): void
    {
        $result = Rfc7230::parseHeaderPairs($rawHeaders);
        $this->assertSame($result, $expectedResult);
    }

    /** @dataProvider provideValidHeaders */
    public function testValidHeaderParsing(string $rawHeaders, array $expectedResult): void
    {
        $result = Rfc7230::parseHeaders($rawHeaders);
        $headers = convertHeaderPairsToMap($expectedResult);
        $this->assertSame($result, $headers);
    }

    /** @dataProvider provideValidHeaders */
    public function testValidHeaderPairFormatting(string $rawHeaders /* ignored for this case */, array $expectedResult): void
    {
        $result = Rfc7230::parseHeaderPairs(Rfc7230::formatHeaderPairs($expectedResult));
        $this->assertSame($result, $expectedResult);
    }

    /** @dataProvider provideValidHeaders */
    public function testValidHeaderFormattingDifferentCasing(string $rawHeaders /* ignored for this case */, array $expectedResult): void
    {
        $headers = [];
        foreach ($expectedResult as [$name, $value]) {
            // No strtolower
            $headers[$name] = $headers[$name] ?? [];
            $headers[$name][] = $value;
        }

        $result = Rfc7230::parseHeaderPairs(Rfc7230::formatHeaders($headers));
        $this->assertSame($result, $expectedResult);
    }

    public function provideValidHeaders(): array
    {
        return [
            ["x:y\r\n", [["x", "y"]]],
            ["server:\tamphp.org\r\n", [["server", "amphp.org"]]],
            ["server: \tamphp.org  \t \r\n", [["server", "amphp.org"]]],
            ["server: \tamphp.org  \t \r\nServer: amphp.org\r\n", [["server", "amphp.org"], ["Server", "amphp.org"]]],
            ["ser124ver:\tamphp.org\r\n", [["ser124ver", "amphp.org"]]],
            ["123: 321\r\n", [['123', '321']]],
            ["AbC: Test\r\n", [['AbC', 'Test']]],
        ];
    }

    /** @dataProvider provideInvalidHeaders */
    public function testInvalidHeaderParsing(string $rawHeaders): void
    {
        $this->expectException(InvalidHeaderException::class);
        Rfc7230::parseHeaders($rawHeaders);
    }

    public function provideInvalidHeaders(): array
    {
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

    public function testThrowsOnHttp2PseudoHeaders(): void
    {
        $headers = [
            "foobar" => ["bar"],
            ":method" => ["GET"],
            "x" => ["y"],
        ];

        $this->expectException(InvalidHeaderException::class);

        Rfc7230::formatHeaders($headers);
    }

    public function testDetectsHeaderInjectionsWithLfInValue(): void
    {
        $this->expectException(InvalidHeaderException::class);
        $this->expectExceptionMessage("Invalid headers");

        Rfc7230::formatHeaders(["foobar" => ["test\nbar"]]);
    }

    public function testDetectsHeaderInjectionsWithCrInValue(): void
    {
        $this->expectException(InvalidHeaderException::class);
        $this->expectExceptionMessage("Invalid headers");

        Rfc7230::formatHeaders(["foobar" => ["test\rbar"]]);
    }

    public function testDetectsHeaderInjectionsWithCrLfInValue(): void
    {
        $this->expectException(InvalidHeaderException::class);
        $this->expectExceptionMessage("Invalid headers");

        Rfc7230::formatHeaders(["foobar" => ["test\r\nbar"]]);
    }

    public function testDetectsHeaderInjectionsWithCrLfAndColonInValue(): void
    {
        $this->expectException(InvalidHeaderException::class);
        $this->expectExceptionMessage("Invalid headers");

        Rfc7230::formatHeaders(["foobar" => ["test\r\nfoo: bar"]]);
    }

    public function testDetectsHeaderInjectionsWithCrInName(): void
    {
        $this->expectException(InvalidHeaderException::class);
        $this->expectExceptionMessage("Invalid headers");

        Rfc7230::formatHeaders(["foobar\rfoobar" => ["bar"]]);
    }

    public function testDetectsHeaderInjectionsWithLfInName(): void
    {
        $this->expectException(InvalidHeaderException::class);
        $this->expectExceptionMessage("Invalid headers");

        Rfc7230::formatHeaders(["foobar\nfoobar" => ["bar"]]);
    }

    public function testDetectsHeaderInjectionsWithCrLfInName(): void
    {
        $this->expectException(InvalidHeaderException::class);
        $this->expectExceptionMessage("Invalid headers");

        Rfc7230::formatHeaders(["foobar\r\nfoobar" => ["bar"]]);
    }

    public function testDetectsHeaderInjectionsWithCrLfAndColonInName(): void
    {
        $this->expectException(InvalidHeaderException::class);
        $this->expectExceptionMessage("Invalid headers");

        Rfc7230::formatHeaders(["foobar: test\r\nfoobar" => ["bar"]]);
    }

    public function testDetectsInvalidHeaderSyntax(): void
    {
        $this->expectException(InvalidHeaderException::class);
        $this->expectExceptionMessage("Invalid headers");

        Rfc7230::formatHeaders(["foo bar" => ["bar"]]);
    }
}
