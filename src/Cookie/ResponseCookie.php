<?php

namespace Amp\Http\Cookie;

final class ResponseCookie {
    /** @var string */
    private $name;

    /** @var string */
    private $value;

    /** @var CookieAttributes */
    private $attributes;

    /**
     * Parses a cookie from a 'set-cookie' header.
     *
     * @param string $string Valid 'set-cookie' header line.
     *
     * @return self|null Returns a `ResponseCookie` instance on success and `null` on failure.
     */
    public static function fromHeader(string $string) { /* : ?self */
        $parts = \array_map("trim", \explode(";", $string));

        $pattern = '(^([^()<>@,;:\\"/[\]?={}\x01-\x20\x7F]++)=([\x21\x23-\x2B\x2D-\x3A\x3C-\x5B\x5D-\x7E]*+)$)m';

        if (!\preg_match($pattern, \array_shift($parts), $match)) {
            return null;
        }

        list(, $name, $value) = $match;

        // httpOnly must default to false for parsing
        $meta = CookieAttributes::empty();

        foreach ($parts as $part) {
            $pieces = \array_map('trim', \explode('=', $part, 2));
            $key = \strtolower($pieces[0]);

            if (1 === \count($pieces)) {
                switch ($key) {
                    case 'secure':
                        $meta = $meta->withSecure();
                        break;

                    case 'httponly':
                        $meta = $meta->withHttpOnly();
                        break;
                }
            } else {
                switch ($key) {
                    case 'expires':
                        $time = \DateTime::createFromFormat('D, j M Y G:i:s T', $pieces[1]);

                        if ($time === false) {
                            break;
                        }

                        $expires = $meta->getExpires();

                        if ($expires === 0 || $expires > $time->getTimestamp()) {
                            $meta = $meta->withExpiry($time);
                        }
                        break;

                    case 'max-age':
                        $maxAge = \trim($pieces[1]);

                        if (!\ctype_digit($maxAge)) {
                            break;
                        }

                        $time = \time() + (int) $maxAge;
                        $expires = $meta->getExpires();

                        if ($expires === 0 || $expires > $time) {
                            $meta = $meta->withMaxAge($maxAge);
                        }
                        break;

                    case 'path':
                        $meta = $meta->withPath($pieces[1]);
                        break;

                    case 'domain':
                        $meta = $meta->withDomain($pieces[1]);
                        break;
                }
            }
        }

        try {
            return new self($name, $value, $meta);
        } catch (InvalidCookieError $e) {
            return null;
        }
    }

    /**
     * @param string           $name
     * @param string           $value
     * @param CookieAttributes $attributes
     *
     * @throws InvalidCookieError If name or value is invalid.
     */
    public function __construct(
        string $name,
        string $value = '',
        CookieAttributes $attributes = null
    ) {
        if (!\preg_match('(^[^()<>@,;:\\\"/[\]?={}\x01-\x20\x7F]++$)', $name)) {
            throw new InvalidCookieError("Invalid cookie name: '{$name}'");
        }

        if (!\preg_match('(^[\x21\x23-\x2B\x2D-\x3A\x3C-\x5B\x5D-\x7E]*+$)', $value)) {
            throw new InvalidCookieError("Invalid cookie value: '{$value}'");
        }

        $this->name = $name;
        $this->value = $value;
        $this->attributes = $attributes ?? CookieAttributes::default();
    }

    public function getExpires(): int {
        return $this->attributes->getExpires();
    }

    public function getPath(): string {
        return $this->attributes->getPath();
    }

    public function getDomain(): string {
        return $this->attributes->getDomain();
    }

    public function isSecure(): bool {
        return $this->attributes->isSecure();
    }

    public function isHttpOnly(): bool {
        return $this->attributes->isHttpOnly();
    }

    public function getAttributes(): CookieAttributes {
        return $this->attributes;
    }

    public function __toString(): string {
        $line = \rawurlencode($this->name) . '=' . \rawurlencode($this->value);
        $line .= $this->attributes;

        return $line;
    }
}
