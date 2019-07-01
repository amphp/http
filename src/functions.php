<?php

namespace Amp\Http;

/**
 * @param Message $message
 * @param string  $headerName
 *
 * @return array|null
 *
 * @see https://tools.ietf.org/html/rfc7230#section-3.2.6
 */
function parseFieldValueComponents(Message $message, string $headerName)
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
            $value = \preg_replace('(\\\\(.))', '\1', $value);
        }

        $pairs[] = [$key, $value];
    }

    if ($totalMatchedLength !== \strlen($header)) {
        return null; // parse error
    }

    return $pairs;
}
