<?php

namespace Amp\Http\Parser;

/**
 * @link https://tools.ietf.org/html/rfc7230
 * @link https://tools.ietf.org/html/rfc2616
 * @link https://tools.ietf.org/html/rfc5234
 */
final class Rfc7230 {
    // We make use of possessive modifiers, which gives a slight performance boost
    const HEADER_REGEX = "(^([^()<>@,;:\\\"/[\]?={}\x01-\x20\x7F]++):[\x20\x09]*+((?:[\x20\x09]*+[\x21-\x7E\x80-\xFF]++)*+)[\x20\x09]*+\r\n)xm";
    const HEADER_FOLD_REGEX = "(\r\n[ \t]++)";

    /**
     * Parses headers according to RFC 7230 and 2616.
     *
     * Allows empty header values, as HTTP/1.0 allows that.
     *
     * @param string $rawHeaders
     *
     * @return array Associative array mapping header names to arrays of values.
     *
     * @throws ParseException If invalid headers have been passed.
     */
    public static function parseHeaders(string $rawHeaders): array {
        // Ensure that the last line also ends with a newline, this is important.
        \assert(\substr($rawHeaders, -2) === "\r\n", "Argument 1 must end with CRLF");

        /** @var array[] $matches */
        $count = \preg_match_all(self::HEADER_REGEX, $rawHeaders, $matches, \PREG_SET_ORDER);

        // If these aren't the same, then one line didn't match and there's an invalid header.
        if ($count !== \substr_count($rawHeaders, "\r\n")) {
            // Folding is deprecated, see https://tools.ietf.org/html/rfc7230#section-3.2.4
            if (\preg_match(self::HEADER_FOLD_REGEX, $rawHeaders)) {
                throw new ParseException("Invalid Header Syntax: Obsolete Line Folding");
            }

            throw new ParseException("Invalid Header Syntax");
        }

        $headers = [];

        foreach ($matches as $match) {
            // We avoid a call to \trim() here due to the regex.
            // Unfortunately, we can't avoid the \strtolower() calls due to \array_change_key_case() behavior.
            // Accessing matches directly instead of using foreach (... as list(...)) is slightly faster.
            $headers[\strtolower($match[1])][] = $match[2];
        }

        return $headers;
    }
}
