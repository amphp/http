<?php

namespace Amp\Http\Cookie;

final class CookieMeta {
    /** @var string */
    private $path = '';

    /** @var string */
    private $domain = '';

    /** @var int */
    private $expires = 0;

    /** @var bool */
    private $secure = false;

    /** @var bool */
    private $httpOnly = true;

    public static function empty(): self {
        $new = new self;
        $new->httpOnly = false;

        return $new;
    }

    public static function default(): self {
        return new self;
    }

    private function __construct() {
        // only allow creation via named constructors
    }

    public function withPath(string $path): self {
        $new = clone $this;
        $new->path = $path;

        return $new;
    }

    public function withDomain(string $domain): self {
        $new = clone $this;
        $new->domain = $domain;

        return $new;
    }

    public function withMaxAge(int $maxAge): self {
        $new = clone $this;
        $new->expires = \time() + $maxAge;

        return $new;
    }

    public function withExpiry(\DateTimeInterface $date): self {
        $new = clone $this;
        $new->expires = $date->getTimestamp();

        return $new;
    }

    public function withoutExpiry(): self {
        $new = clone $this;
        $new->expires = 0;

        return $new;
    }

    public function withSecure(): self {
        $new = clone $this;
        $new->secure = true;

        return $new;
    }

    public function withoutSecure(): self {
        $new = clone $this;
        $new->secure = false;

        return $new;
    }

    public function withHttpOnly(): self {
        $new = clone $this;
        $new->httpOnly = true;

        return $new;
    }

    public function withoutHttpOnly(): self {
        $new = clone $this;
        $new->httpOnly = false;

        return $new;
    }

    public function getPath(): string {
        return $this->path;
    }

    public function getDomain(): string {
        return $this->domain;
    }

    public function getExpires(): int {
        return $this->expires;
    }

    public function isSecure(): bool {
        return $this->secure;
    }

    public function isHttpOnly(): bool {
        return $this->httpOnly;
    }
}
