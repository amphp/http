<?php declare(strict_types=1);

namespace Amp\Http;

abstract class HttpResponse extends HttpMessage
{
    private string $reason;

    public function __construct(
        private int $status,
        ?string $reason = null,
    ) {
        $this->status = $this->validateStatusCode($status);
        $this->reason = $reason ?? HttpStatus::getReason($status);
    }

    /**
     * Retrieve the response's three-digit HTTP status code.
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * Retrieve the response's (possibly empty) reason phrase.
     */
    public function getReason(): string
    {
        return $this->reason;
    }

    protected function setStatus(int $status, ?string $reason = null): void
    {
        $this->status = $this->validateStatusCode($status);
        $this->reason = $reason ?? HttpStatus::getReason($status);
    }

    private function validateStatusCode(int $status): int
    {
        if ($status < 100 || $status > 599) {
            throw new \ValueError(
                'Invalid status code. Must be an integer between 100 and 599, inclusive.'
            );
        }

        return $status;
    }
}
