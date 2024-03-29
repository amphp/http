<?php declare(strict_types=1);

namespace Amp\Http;

class ParseHeaderFieldsTest extends HeaderParsingTest
{
    public function provideMultipleHeaderCases(): iterable
    {
        yield [
            'no-cache, no-store, must-revalidate',
            [
                ['no-cache' => ''],
                ['no-store' => ''],
                ['must-revalidate' => ''],
            ],
        ];

        yield [
            'Upgrade',
            [
                ['upgrade' => ''],
            ],
        ];

        yield [
            'public, max-age=31536000',
            [
                ['public' => ''],
                ['max-age' => '31536000'],
            ],
        ];

        yield [
            'Public, max-Age=31536000',
            [
                ['public' => ''],
                ['max-age' => '31536000'],
            ],
        ];

        yield [
            'private="foo, bar", max-age=31536000',
            [
                ['private' => 'foo, bar'],
                ['max-age' => '31536000'],
            ],
        ];

        yield [
            'private="foo, bar, max-age=31536000',
            null,
        ];

        yield [
            'private="foo\"bar", max-age=31536000',
            [
                ['private' => 'foo"bar'],
                ['max-age' => '31536000'],
            ],
        ];

        yield [
            'private="foo\"\"bar", max-age=31536000',
            [
                ['private' => 'foo""bar'],
                ['max-age' => '31536000'],
            ],
        ];

        yield [
            'private="foo\\\\", bar',
            [
                ['private' => 'foo\\'],
                ['bar' => ''],
            ],
        ];

        yield [
            'private="foo", private=bar',
            [
                ['private' => 'foo'],
                ['private' => 'bar'],
            ],
        ];

        yield [
            'by="fake;proxy";for="127.0.0.1";proto=https, by=nginx;for="172.18.0.1";proto=http',
            [
                ['by' => 'fake;proxy', 'for' => '127.0.0.1', 'proto' => 'https'],
                ['by' => 'nginx', 'for' => '172.18.0.1', 'proto' => 'http'],
            ],
        ];
    }

    public function provideTokenCases(): iterable
    {
        yield [
            'no-cache, no-store, must-revalidate',
            ['no-cache', 'no-store', 'must-revalidate'],
        ];

        yield [
            'Upgrade',
            ['upgrade'],
        ];

        yield [
            'public, max-age=31536000',
            null,
        ];
    }

    /**
     * @dataProvider provideMultipleHeaderCases
     */
    public function test(string $header, ?array $expected): void
    {
        $headerName = 'test-header';
        $message = $this->createMessage([$headerName => [$header]]);
        self::assertSame($expected, parseMultipleHeaderFields($message, $headerName));
    }

    /**
     * @dataProvider provideTokenCases
     */
    public function testParseHeaderTokens(string $header, ?array $expected): void
    {
        $headerName = 'test-header';
        $message = $this->createMessage([$headerName => [$header]]);
        self::assertSame($expected, parseHeaderTokens($message, $headerName));
    }
}
