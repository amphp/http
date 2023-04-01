<?php declare(strict_types=1);

namespace Amp\Http;

use PHPUnit\Framework\TestCase;

abstract class HeaderParsingTest extends TestCase
{
    protected function createMessage(array $headers): HttpMessage
    {
        return new class($headers) extends HttpMessage {
            public function __construct(array $headers)
            {
                $this->setHeaders($headers);
            }
        };
    }
}
