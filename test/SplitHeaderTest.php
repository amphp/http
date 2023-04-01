<?php declare(strict_types=1);

namespace Amp\Http;

use PHPUnit\Framework\TestCase;

class SplitHeaderTest extends TestCase
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

    private function createMessage(array $headers): HttpMessage
    {
        return new class($headers) extends HttpMessage {
            public function __construct(array $headers)
            {
                $this->replaceHeaders($headers);
            }
        };
    }
}
