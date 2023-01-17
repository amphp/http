<?php declare(strict_types=1);

namespace Amp\Http;

use const Amp\Http\Internal\HEADER_LOWER;

/**
 * @link https://tools.ietf.org/html/rfc7230
 * @link https://tools.ietf.org/html/rfc2616
 * @link https://tools.ietf.org/html/rfc5234
 */
final class Rfc7230
{
    // We make use of possessive modifiers, which gives a slight performance boost
    public const HEADER_NAME_REGEX = "(^([^()<>@,;:\\\"/[\]?={}\x01-\x20\x7F]++)$)";
    public const HEADER_VALUE_REGEX = "(^[ \t]*+((?:[ \t]*+[\x21-\x7E\x80-\xFF]++)*+)[ \t]*+$)";
    public const HEADER_REGEX = "(^([^()<>@,;:\\\"/[\]?={}\x01-\x20\x7F]++):[ \t]*+((?:[ \t]*+[\x21-\x7E\x80-\xFF]++)*+)[ \t]*+\r\n)m";
    public const HEADER_FOLD_REGEX = "(\r\n[ \t]++)";

    private const HEADER_SPRINTF = [
        0 => "",
        1 => "%s: %s\r\n",
        2 => "%s: %s\r\n%s: %s\r\n",
        3 => "%s: %s\r\n%s: %s\r\n%s: %s\r\n",
        4 => "%s: %s\r\n%s: %s\r\n%s: %s\r\n%s: %s\r\n",
        5 => "%s: %s\r\n%s: %s\r\n%s: %s\r\n%s: %s\r\n%s: %s\r\n",
        6 => "%s: %s\r\n%s: %s\r\n%s: %s\r\n%s: %s\r\n%s: %s\r\n%s: %s\r\n",
        7 => "%s: %s\r\n%s: %s\r\n%s: %s\r\n%s: %s\r\n%s: %s\r\n%s: %s\r\n%s: %s\r\n",
        8 => "%s: %s\r\n%s: %s\r\n%s: %s\r\n%s: %s\r\n%s: %s\r\n%s: %s\r\n%s: %s\r\n%s: %s\r\n",
        9 => "%s: %s\r\n%s: %s\r\n%s: %s\r\n%s: %s\r\n%s: %s\r\n%s: %s\r\n%s: %s\r\n%s: %s\r\n%s: %s\r\n",
        10 => "%s: %s\r\n%s: %s\r\n%s: %s\r\n%s: %s\r\n%s: %s\r\n%s: %s\r\n%s: %s\r\n%s: %s\r\n%s: %s\r\n%s: %s\r\n",
    ];

    /**
     * Parses headers according to RFC 7230 and 2616.
     *
     * Allows empty header values, as HTTP/1.0 allows that.
     *
     * @return array Associative array mapping header names to arrays of values.
     *
     * @throws InvalidHeaderException If invalid headers have been passed.
     */
    public static function parseHeaders(string $rawHeaders): array
    {
        $headers = [];

        foreach (self::parseRawHeaders($rawHeaders) as $header) {
            // Unfortunately, we can't avoid the \strtolower() calls due to \array_change_key_case() behavior
            // when equal headers are present with different casing, e.g. 'set-cookie' and 'Set-Cookie'.
            // Accessing headers directly instead of using foreach (... as list(...)) is slightly faster.
            $headers[HEADER_LOWER[$header[0]] ?? \strtolower($header[0])][] = $header[1];
        }

        return $headers;
    }

    /**
     * Parses headers according to RFC 7230 and 2616.
     *
     * Allows empty header values, as HTTP/1.0 allows that.
     *
     * @return list<array{string, string}> List of [field, value] header pairs.
     *
     * @throws InvalidHeaderException If invalid headers have been passed.
     */
    public static function parseRawHeaders(string $rawHeaders): array
    {
        // Ensure that the last line also ends with a newline, this is important.
        \assert(\str_ends_with($rawHeaders, "\r\n"), "Argument 1 must end with CRLF: " . \bin2hex($rawHeaders));

        /** @var list<int, list<string>> $matches */
        $count = \preg_match_all(self::HEADER_REGEX, $rawHeaders, $matches, \PREG_SET_ORDER);

        // If these aren't the same, then one line didn't match and there's an invalid header.
        if ($count !== \substr_count($rawHeaders, "\n")) {
            // Folding is deprecated, see https://tools.ietf.org/html/rfc7230#section-3.2.4
            if (\preg_match(self::HEADER_FOLD_REGEX, $rawHeaders)) {
                throw new InvalidHeaderException("Invalid header syntax: Obsolete line folding");
            }

            throw new InvalidHeaderException("Invalid header syntax");
        }

        $headers = [];

        foreach ($matches as $match) {
            // We avoid a call to \trim() here due to the regex.
            // Accessing matches directly instead of using foreach (... as list(...)) is slightly faster.
            $headers[] = [$match[1], $match[2]];
        }

        return $headers;
    }

    /**
     * Format headers in to their on-the-wire format.
     *
     * Headers are always validated syntactically. This protects against response splitting and header injection
     * attacks.
     *
     * @param array $headers Headers in a format as returned by {@see parseHeaders()}.
     *
     * @return string Formatted headers.
     *
     * @throws InvalidHeaderException If header names or values are invalid.
     */
    public static function formatHeaders(array $headers): string
    {
        $headerList = [];

        foreach ($headers as $name => $values) {
            foreach ($values as $value) {
                // PHP casts integer-like keys to integers
                $headerList[] = [(string) $name, (string) $value];
            }
        }

        return self::formatRawHeaders($headerList);
    }

    /**
     * Format headers in to their on-the-wire HTTP/1 format.
     *
     * Headers are always validated syntactically. This protects against response splitting and header injection
     * attacks.
     *
     * @param list<array{0: string, 1: string}> $headers List of headers in [field, value] format as returned by
     * {@see HttpMessage::getRawHeaders()}.
     *
     * @return string Formatted headers.
     *
     * @throws InvalidHeaderException If header names or values are invalid.
     */
    public static function formatRawHeaders(array $headers): string
    {
        $lines = \count($headers);
        $bytes = \sprintf(
            self::HEADER_SPRINTF[$lines] ?? \str_repeat(self::HEADER_SPRINTF[1], $lines),
            ...\array_merge(...$headers),
        );
        $count = \preg_match_all(self::HEADER_REGEX, $bytes);

        if ($lines !== $count || $lines !== \substr_count($bytes, "\n")) {
            throw new InvalidHeaderException("Invalid headers");
        }

        return $bytes;
    }
}
