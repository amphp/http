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
    $pairs = [];

    foreach ($matches as $match) {
        $totalMatchedLength += \strlen($match[0]);

        // case-insensitive, see https://tools.ietf.org/html/rfc7234.html#section-5.2
        $key = \strtolower(\trim($match[1]));
        $value = ($match[2] ?? '') . \trim($match[3] ?? '');

        if (isset($match[3]) && $match[3] !== '' && \strpos($match[3], '"') !== false) {
            return null; // parse error, unclosed quote
        }

        $pairs[$key] = $value;
    }

    if ($totalMatchedLength !== \strlen($header)) {
        return null; // parse error
    }

    return $pairs;
}
