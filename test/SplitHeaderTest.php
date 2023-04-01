<?php declare(strict_types=1);

namespace Amp\Http;

class SplitHeaderTest extends HeaderParsingTest
{
    public function provideCases(): iterable
    {
        yield [
            ['text/html, application/xhtml+xml, application/xml;q=0.9, image/webp, */*;q=0.8'],
            [
                'text/html',
                'application/xhtml+xml',
                'application/xml;q=0.9',
                'image/webp',
                '*/*;q=0.8',
            ],
        ];

        yield [
            ['test="value,with,commas", test=unquoted, test="quoted\\",with\\",escaped"'],
            [
                'test="value,with,commas"',
                'test=unquoted',
                'test="quoted\\",with\\",escaped"',
            ],
        ];
    }

    /**
     * @dataProvider provideCases
     */
    public function test(array $headers, array $expected): void
    {
        $headerName = 'test-header';
        $message = $this->createMessage([$headerName => $headers]);
        self::assertSame($expected, splitHeader($message, $headerName));
    }
}
