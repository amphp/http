<?php declare(strict_types=1);

namespace Amp\Http;

use Amp\Http\Http1\Rfc7230;

/**
 * Creates a list of key-value pairs from each comma-separated header value.
 *
 * @param non-empty-string $headerName
 *
 * @return list<list<array{non-empty-string, string|null}>>|null
 */
function parseFieldValueComponents(HttpMessage $message, string $headerName): ?array
{
    $headers = splitHeader($message, $headerName);
    if ($headers === null) {
        return null;
    }

    $sets = [];
    foreach ($headers as $header) {
        $sets[] = $pairs = parseFieldValuePairs($header);
        if ($pairs === null) {
            return null;
        }
    }

    return $sets;
}

/**
 * Splits comma-separated fields into individual components.
 *
 * @param non-empty-string $headerName
 *
 * @return list<string>|null
 */
function splitHeader(HttpMessage $message, string $headerName): ?array
{
    $header = \implode(', ', $message->getHeaderArray($headerName));

    if ($header === '') {
        return [];
    }

    $positions = [];
    $withinQuotes = false;
    $length = \strlen($header);
    for ($i = 0; $i < $length; ++$i) {
        match ($header[$i]) {
            '\\' => ++$i, // Skip next character
            '"' => $withinQuotes = !$withinQuotes,
            ',' => $withinQuotes ? null : $positions[] = $i,
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
    foreach ($positions as $position) {
        $headers[] = \substr($header, $offset, $position - $offset);
        $offset = $position + 1;
    }
    $headers[] = \substr($header, $offset);

    return \array_map(\trim(...), $headers);
}

/**
 *
 * @see https://tools.ietf.org/html/rfc7230#section-3.2.6
 *
 * @return list<array{non-empty-string, string|null}>|null
 */
function parseFieldValuePairs(string $header): ?array
{
    \preg_match_all(
        '((?:^|;\s*)([^=]+)(?:=(?:"((?:[^\\\\"]|\\\\\\\\|\\\\")*)"|([^";]+)))?\s*)',
        $header,
        $matches,
        \PREG_SET_ORDER,
    );

    $totalMatchedLength = 0;
    $pairs = [];

    foreach ($matches as $match) {
        $totalMatchedLength += \strlen($match[0]);

        $key = \trim($match[1]);
        $value = $match[3] ?? $match[2] ?? null;

        if (($match[2] ?? '') !== '') {
            // decode escaped characters
            $value = (string) \preg_replace('(\\\\(.))', '\1', $value);
        }

        \assert($key !== '');
        $pairs[] = [$key, $value];
    }

    if ($totalMatchedLength !== \strlen($header)) {
        return null; // parse error
    }

    return $pairs;
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
