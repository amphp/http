<?php declare(strict_types=1);

namespace Amp\Http;

abstract class HttpResponse extends HttpMessage
{
    private string $reason;

    public function __construct(
        private int $status,
        ?string $reason = null,
    ) {
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
        $this->status = $status;
        $this->reason = $reason ?? HttpStatus::getReason($status);
    }
}
