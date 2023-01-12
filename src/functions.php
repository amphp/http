<?php declare(strict_types=1);

namespace Amp\Http;

/**
 * @see https://tools.ietf.org/html/rfc7230#section-3.2.6
 *
 * @return list<array{non-empty-string, string}>|null
 */
function parseFieldValueComponents(HttpMessage $message, string $headerName): ?array
{
    $header = \implode(', ', $message->getHeaderArray($headerName));

    if ($header === '') {
        return [];
    }

    \preg_match_all('(([^"=,]+)(?:=(?:"((?:[^\\\\"]|\\\\.)*)"|([^,"]*)))?,?\s*)', $header, $matches, \PREG_SET_ORDER);

    $totalMatchedLength = 0;
    $pairs = [];

    foreach ($matches as $match) {
        $totalMatchedLength += \strlen($match[0]);

        $key = \trim($match[1]);
        $value = ($match[2] ?? '') . \trim($match[3] ?? '');

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
 * @param list<array{non-empty-string, string}>|null $pairs Output of {@see parseFieldValueComponents()}.
 *      Keys are handled case-insensitively.
 *
 * @return array<non-empty-string, string>|null Map of keys to values or {@code null} if incompatible duplicates are found.
 */
function createFieldValueComponentMap(?array $pairs): ?array
{
    if ($pairs === null) {
        return null;
    }

    $map = [];

    /** @psalm-suppress RedundantCondition */
    \assert((static function () use ($pairs): bool {
        foreach ($pairs as $pair) {
            \assert(\count($pair) === 2);
            \assert(\is_string($pair[0]));
            \assert(\is_string($pair[1]));
        }

        return true;
    })());

    foreach ($pairs as [$key, $value]) {
        $key = HttpMessage::HEADER_LOWER[$key] ?? \strtolower($key);

        if (isset($map[$key]) && $map[$key] !== $value) {
            return null; // incompatible duplicates
        }

        $map[$key] = $value;
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
