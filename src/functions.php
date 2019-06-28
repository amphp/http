<?php

namespace Amp\Http;

/**
 * @param Message $message
 * @param string  $headerName
 *
 * @return array|null
 */
function parseTokenListHeader(Message $message, string $headerName)
{
    $header = \implode(', ', $message->getHeaderArray($headerName));

    if ($header === '') {
        return [];
    }

    \preg_match_all('(([^=,]+)(?:=(?:"([^"]*)"|([^,]*)))?,?\s*)', $header, $matches, \PREG_SET_ORDER);

    $totalMatchedLength = 0;
    $pairs = \array_map(static function ($match) use (&$totalMatchedLength) {
        $totalMatchedLength += \strlen($match[0]);

        // case-insensitive, see https://tools.ietf.org/html/rfc7234.html#section-5.2
        return [\strtolower(\trim($match[1])), \trim(($match[2] ?? '') . ($match[3] ?? ''))];
    }, $matches);

    if ($totalMatchedLength !== \strlen($header)) {
        return null; // parse error
    }

    $result = [];

    foreach ($pairs as list($key, $value)) {
        $result[$key] = $value;
    }

    return $result;
}
