<?php declare(strict_types=1);

namespace Amp\Http;

use Amp\Http\Http1\Rfc7230;

/**
 * Splits comma-separated fields into individual components. Returns null if a syntax error is encountered.
 *
 * For example, the following header
 * Cache-Control: public, max-age=604800, must-revalidate
 * would be parsed to the array
 * ['public', 'max-age=604800', 'must-revalidate']
 *
 * @param non-empty-string $headerName
 * @param non-empty-string $separator Must be a string of length 1.
 *
 * @return list<string>|null
 */
function splitHeader(HttpMessage $message, string $headerName, string $separator = ','): ?array
{
    if (\strlen($separator) !== 1) {
        throw new \ValueError('The separator must be a single byte');
    }

    $header = \implode($separator, $message->getHeaderArray($headerName));

    if ($header === '') {
        return [];
    }

    $positions = [];
    $withinQuotes = false;
    $headerLength = \strlen($header);
    for ($i = 0; $i < $headerLength; ++$i) {
        match ($header[$i]) {
            '\\' => ++$i, // Skip next character
            '"' => $withinQuotes = !$withinQuotes,
            $separator => $withinQuotes ? null : $positions[] = $i,
            default => null,
        };
    }

    if ($withinQuotes) {
        return null;
    }

    if (!$positions) {
        return [$header];
    }

    $offset = 0;
    $headers = [];
    $separatorLength = \strlen($separator);
    foreach ($positions as $position) {
        $headers[] = \substr($header, $offset, $position - $offset);
        $offset = $position + $separatorLength;
    }
    $headers[] = \substr($header, $offset);

    return \array_map(\trim(...), $headers);
}

/**
 * Parses a list of key-value pairs from each comma-separated header value. Returns null if a syntax error is
 * encountered.
 *
 * For example, the following header
 * Forwarded: for="172.18.0.1";proto=https, for="172.25.0.1";proto=http
 * would be parsed to the array
 * `[['for' => '172.18.0.1', 'proto' => 'https'], ['for' => '172.25.0.1', 'proto' => 'http']]`
 *
 * @param non-empty-string $headerName
 *
 * @return list<array<non-empty-string, string|null>>|null
 */
function parseMultipleHeaderFields(HttpMessage $message, string $headerName): ?array
{
    $headers = splitHeader($message, $headerName);
    if ($headers === null) {
        return null;
    }

    $maps = [];
    foreach ($headers as $header) {
        $map = parseSingleHeaderFields($header);
        if ($map === null) {
            return null;
        }

        $maps[] = $map;
    }

    return $maps;
}

/**
 * Parse a single header into key-value pairs.
 *
 * @see https://tools.ietf.org/html/rfc7230#section-3.2.6
 *
 * @return array<non-empty-string, string|null>|null
 */
function parseSingleHeaderFields(string $header): ?array
{
    \preg_match_all(
        '((?:^|;\s*)([^=]+)(?:=(?:"((?:[^\\\\"]|\\\\\\\\|\\\\")*)"|([^";]+)))?\s*)',
        $header,
        $matches,
        \PREG_SET_ORDER,
    );

    $totalMatchedLength = 0;
    $map = [];

    foreach ($matches as $match) {
        $totalMatchedLength += \strlen($match[0]);

        $key = \trim($match[1]);
        $value = $match[3] ?? $match[2] ?? null;

        if (($match[2] ?? '') !== '') {
            // decode escaped characters
            $value = (string) \preg_replace('(\\\\(.))', '\1', $match[2]);
        }

        \assert($key !== '');
        $map[$key] = $value;
    }

    if ($totalMatchedLength !== \strlen($header)) {
        return null; // parse error
    }

    return $map;
}

/**
 * Format timestamp in seconds as an HTTP date header.
 *
 * @param int|null $timestamp Timestamp to format, current time if `null`.
 *
 * @return string Formatted date header value.
 */
function formatDateHeader(?int $timestamp = null): string
{
    static $cachedTimestamp, $cachedFormattedDate;

    $timestamp = $timestamp ?? \time();
    if ($cachedTimestamp === $timestamp) {
        return $cachedFormattedDate;
    }

    return $cachedFormattedDate = \gmdate("D, d M Y H:i:s", $cachedTimestamp = $timestamp) . " GMT";
}

/**
 * Convert the output of {@see Rfc7230::parseHeaderPairs()} or {@see HttpMessage::getHeaderPairs()} into the structure
 * returned by {@see Rfc7230::parseHeaders()} or {@see HttpMessage::getHeaders()}.
 *
 * @param list<array{non-empty-string, string}> $pairs
 *
 * @return array<non-empty-string, list<string>>
 */
function convertHeaderPairsToMap(array $pairs): array
{
    $headers = [];

    foreach ($pairs as $header) {
        /** @psalm-suppress RedundantCondition */
        \assert(
            \count($header) === 2
            && \array_is_list($header)
            && \is_string($header[0])
            && \is_string($header[1])
        );

        $headers[Internal\HEADER_LOWERCASE_MAP[$header[0]] ?? \strtolower($header[0])][] = $header[1];
    }

    return $headers;
}
