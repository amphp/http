<?php declare(strict_types=1);

namespace Amp\Http;

use PHPUnit\Framework\TestCase;

class HttpStatusTest extends TestCase
{
    public function testEachDefinedStatusHasDefaultReason(): void
    {
        $class = new \ReflectionClass(HttpStatus::class);

        foreach ($class->getConstants() as $statusCode) {
            $this->assertNotEmpty(HttpStatus::getReason($statusCode), "{$statusCode} doesn't have a default reason.");
        }
    }

    public function testEachDefaultReasonHasCorrespondingConstant(): void
    {
        $class = new \ReflectionClass(HttpStatus::class);
        $constants = $class->getConstants();

        for ($i = 0; $i < 600; $i++) {
            $reason = HttpStatus::getReason($i);

            if ($reason !== "") {
                $this->assertContains($i, $constants);
            }
        }
    }

    public function testNoDuplicateDefinition(): void
    {
        $class = new \ReflectionClass(HttpStatus::class);
        $constants = $class->getConstants();

        // Double array_flip removes any duplicates.
        $this->assertSame($constants, \array_flip(\array_flip($constants)));
    }
}
