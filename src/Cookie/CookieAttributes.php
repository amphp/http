<?php

namespace Amp\Http\Cookie;

/**
 * Cookie attributes as defined in https://tools.ietf.org/html/rfc6265.
 *
 * @link https://tools.ietf.org/html/rfc6265
 */
final class CookieAttributes {
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

    /**
     * @return CookieAttributes No cookie attributes.
     *
     * @see self::default()
     */
    public static function empty(): self {
        $new = new self;
        $new->httpOnly = false;

        return $new;
    }

    /**
     * @return CookieAttributes Default cookie attributes, which means httpOnly is enabled by default.
     *
     * @see self::empty()
     */
    public static function default(): self {
        return new self;
    }

    private function __construct() {
        // only allow creation via named constructors
    }

    /**
     * @param string $path Cookie path.
     *
     * @return self Cloned instance with the specified operation applied. Cloned instance with the specified operation applied.
     *
     * @link https://tools.ietf.org/html/rfc6265#section-5.2.4
     */
    public function withPath(string $path): self {
        $new = clone $this;
        $new->path = $path;

        return $new;
    }

    /**
     * @param string $domain Cookie domain.
     *
     * @return self Cloned instance with the specified operation applied.
     *
     * @link https://tools.ietf.org/html/rfc6265#section-5.2.3
     */
    public function withDomain(string $domain): self {
        $new = clone $this;
        $new->domain = $domain;

        return $new;
    }

    /**
     * Applies the given maximum age to the cookie.
     *
     * Maximum age and expiry are normalized by this class. Changing one affects the other.
     *
     * @param int $maxAge Cookie maximum age.
     *
     * @return self Cloned instance with the specified operation applied.
     *
     * @see self::withExpiry()
     * @see self::withoutExpiry()
     *
     * @link https://tools.ietf.org/html/rfc6265#section-5.2.2
     */
    public function withMaxAge(int $maxAge): self {
        $new = clone $this;
        $new->expires = \time() + $maxAge;

        return $new;
    }

    /**
     * Applies the given expiry to the cookie.
     *
     * Maximum age and expiry are normalized by this class. Changing one affects the other.
     *
     * @param \DateTimeInterface $date
     *
     * @return self Cloned instance with the specified operation applied.
     *
     * @see self::withMaxAge()
     * @see self::withoutExpiry()
     *
     * @link https://tools.ietf.org/html/rfc6265#section-5.2.1
     */
    public function withExpiry(\DateTimeInterface $date): self {
        $new = clone $this;
        $new->expires = $date->getTimestamp();

        return $new;
    }

    /**
     * Removes any expiry information.
     *
     * Maximum age and expiry are normalized by this class. Changing one affects the other.
     *
     * @return self Cloned instance with the specified operation applied.
     *
     * @see self::withMaxAge()
     * @see self::withExpiry()
     *
     * @link https://tools.ietf.org/html/rfc6265#section-5.2.2
     * @link https://tools.ietf.org/html/rfc6265#section-5.2.1
     */
    public function withoutExpiry(): self {
        $new = clone $this;
        $new->expires = 0;

        return $new;
    }

    /**
     * @return self Cloned instance with the specified operation applied.
     *
     * @see self::withoutSecure()
     *
     * @link https://tools.ietf.org/html/rfc6265#section-5.2.5
     */
    public function withSecure(): self {
        $new = clone $this;
        $new->secure = true;

        return $new;
    }

    /**
     * @return self Cloned instance with the specified operation applied.
     *
     * @see self::withSecure()
     *
     * @link https://tools.ietf.org/html/rfc6265#section-5.2.5
     */
    public function withoutSecure(): self {
        $new = clone $this;
        $new->secure = false;

        return $new;
    }

    /**
     * @return self Cloned instance with the specified operation applied.
     *
     * @see self::withoutHttpOnly()
     *
     * @link https://tools.ietf.org/html/rfc6265#section-5.2.6
     */
    public function withHttpOnly(): self {
        $new = clone $this;
        $new->httpOnly = true;

        return $new;
    }

    /**
     * @return self Cloned instance with the specified operation applied.
     *
     * @see self::withHttpOnly()
     *
     * @link https://tools.ietf.org/html/rfc6265#section-5.2.6
     */
    public function withoutHttpOnly(): self {
        $new = clone $this;
        $new->httpOnly = false;

        return $new;
    }

    /**
     * @return string Cookie path.
     *
     * @link https://tools.ietf.org/html/rfc6265#section-5.2.4
     */
    public function getPath(): string {
        return $this->path;
    }

    /**
     * @return string Cookie domain.
     *
     * @link https://tools.ietf.org/html/rfc6265#section-5.2.3
     */
    public function getDomain(): string {
        return $this->domain;
    }

    /**
     * @return int Expiry as unix timestamp or 0 to indicate no expiry.
     *
     * @link https://tools.ietf.org/html/rfc6265#section-5.2.1
     * @link https://tools.ietf.org/html/rfc6265#section-5.2.2
     */
    public function getExpires(): int {
        return $this->expires;
    }

    /**
     * @return bool Whether the secure flag is enabled or not.
     *
     * @link https://tools.ietf.org/html/rfc6265#section-5.2.5
     */
    public function isSecure(): bool {
        return $this->secure;
    }

    /**
     * @return bool Whether the httpOnly flag is enabled or not.
     *
     * @link https://tools.ietf.org/html/rfc6265#section-5.2.6
     */
    public function isHttpOnly(): bool {
        return $this->httpOnly;
    }

    /**
     * @return string Representation of the cookie attributes appended to key=value in a 'set-cookie' header.
     */
    public function __toString(): string {
        $string = '';

        if (0 !== $this->expires) {
            $string .= '; Expires=' . \gmdate('D, j M Y G:i:s T', $this->expires);
        }

        if ('' !== $this->path) {
            $string .= '; Path=' . \rawurlencode($this->path);
        }

        if ('' !== $this->domain) {
            $string .= '; Domain=' . \rawurlencode($this->domain);
        }

        if ($this->secure) {
            $string .= '; Secure';
        }

        if ($this->httpOnly) {
            $string .= '; HttpOnly';
        }

        return $string;
    }
}
