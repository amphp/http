<?php

namespace Amp\Http\Cookie;

/**
 * A cookie as sent in a response's 'set-cookie' header, so with attributes.
 *
 * This class does not deal with encoding of arbitrary names and values. If you want to use arbitrary values, please use
 * an encoding mechanism like Base64 or URL encoding.
 *
 * @link https://tools.ietf.org/html/rfc6265#section-5.2
 */
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
                            break; // break is correct, see https://tools.ietf.org/html/rfc6265#section-5.2.1
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

        // This won't throw. If it does, then the regex above is wrong.
        return new self($name, $value, $meta);
    }

    /**
     * @param string           $name Name of the cookie.
     * @param string           $value Value of the cookie.
     * @param CookieAttributes $attributes Attributes of the cookie.
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

    /**
     * @return string Name of the cookie.
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return string Value of the cookie.
     */
    public function getValue(): string {
        return $this->value;
    }

    /**
     * @return int Expiry as unix timestamp or 0 to indicate no expiry.
     *
     * @link https://tools.ietf.org/html/rfc6265#section-5.2.1
     * @link https://tools.ietf.org/html/rfc6265#section-5.2.2
     */
    public function getExpires(): int {
        return $this->attributes->getExpires();
    }

    /**
     * @return string Cookie path.
     *
     * @link https://tools.ietf.org/html/rfc6265#section-5.2.4
     */
    public function getPath(): string {
        return $this->attributes->getPath();
    }

    /**
     * @return string Cookie domain.
     *
     * @link https://tools.ietf.org/html/rfc6265#section-5.2.3
     */
    public function getDomain(): string {
        return $this->attributes->getDomain();
    }

    /**
     * @return bool Whether the secure flag is enabled or not.
     *
     * @link https://tools.ietf.org/html/rfc6265#section-5.2.5
     */
    public function isSecure(): bool {
        return $this->attributes->isSecure();
    }

    /**
     * @return bool Whether the httpOnly flag is enabled or not.
     *
     * @link https://tools.ietf.org/html/rfc6265#section-5.2.6
     */
    public function isHttpOnly(): bool {
        return $this->attributes->isHttpOnly();
    }

    /**
     * @return CookieAttributes All cookie attributes.
     */
    public function getAttributes(): CookieAttributes {
        return $this->attributes;
    }

    /**
     * @return string Representation of the cookie as in a 'set-cookie' header.
     */
    public function __toString(): string {
        $line = $this->name . '=' . $this->value;
        $line .= $this->attributes;

        return $line;
    }
}
