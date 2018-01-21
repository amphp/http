<?php

namespace Amp\Http\Cookie;

final class RequestCookie {
    /** @var string */
    private $name;

    /** @var string */
    private $value;

    /**
     * Parses the cookies from a 'cookie' header.
     *
     * Note: Parsing is aborted if there's an invalid value and no cookies are returned.
     *
     * @param string $string Valid 'cookie' header line.
     *
     * @return RequestCookie[]
     */
    public static function fromHeader(string $string): array {
        $cookies = \explode(";", $string);
        $result = [];

        try {
            foreach ($cookies as $cookie) {
                $parts = \explode('=', $cookie, 2);

                if (2 !== \count($parts)) {
                    return [];
                }

                list($name, $value) = $parts;

                // We can safely trim quotes, as they're not allowed within cookie values
                $result[] = new self(\trim($name), \trim($value, " \t\""));
            }
        } catch (InvalidCookieError $e) {
            return [];
        }

        return $result;
    }

    /**
     * @param string $name Cookie name in its decoded form.
     * @param string $value Cookie value in its decoded form.
     *
     * @throws InvalidCookieError If name or value is invalid.
     */
    public function __construct(string $name, string $value = '') {
        if (!\preg_match('(^[^()<>@,;:\\\"/[\]?={}\x01-\x20\x7F]*+$)', $name)) {
            throw new InvalidCookieError("Invalid cookie name: '{$name}'");
        }

        if (!\preg_match('(^[\x21\x23-\x2B\x2D-\x3A\x3C-\x5B\x5D-\x7E]*+$)', $value)) {
            throw new InvalidCookieError("Invalid cookie value: '{$value}'");
        }

        $this->name = $name;
        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue(): string {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string {
        return \rawurlencode($this->name) . '=' . \rawurlencode($this->value);
    }
}
