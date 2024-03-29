<?php declare(strict_types=1);

namespace Amp\Http\Internal;

/** @internal */
const HEADER_LOWERCASE_MAP = [
    'Accept' => 'accept',
    'accept' => 'accept',
    'Accept-Encoding' => 'accept-encoding',
    'accept-encoding' => 'accept-encoding',
    'Accept-Language' => 'accept-language',
    'accept-language' => 'accept-language',
    'Authorization' => 'authorization',
    'authorization' => 'authorization',
    'Cache-Control' => 'cache-control',
    'cache-control' => 'cache-control',
    'Connection' => 'connection',
    'connection' => 'connection',
    'Content-Encoding' => 'content-encoding',
    'content-encoding' => 'content-encoding',
    'Content-Length' => 'content-length',
    'content-length' => 'content-length',
    'Content-Type' => 'content-type',
    'content-type' => 'content-type',
    'Content-Security-Policy' => 'content-security-policy',
    'content-security-policy' => 'content-security-policy',
    'Cookie' => 'cookie',
    'cookie' => 'cookie',
    'Date' => 'date',
    'date' => 'date',
    'Forwarded' => 'forwarded',
    'forwarded' => 'forwarded',
    'Host' => 'host',
    'host' => 'host',
    'Referrer-Policy' => 'referrer-policy',
    'referrer-policy' => 'referrer-policy',
    'Sec-Fetch-Dest' => 'sec-fetch-dest',
    'sec-fetch-dest' => 'sec-fetch-dest',
    'Sec-Fetch-Mode' => 'sec-fetch-mode',
    'sec-fetch-mode' => 'sec-fetch-mode',
    'Sec-Fetch-Site' => 'sec-fetch-site',
    'sec-fetch-site' => 'sec-fetch-site',
    'Sec-Fetch-User' => 'sec-fetch-user',
    'sec-fetch-user' => 'sec-fetch-user',
    'Set-Cookie' => 'set-cookie',
    'set-cookie' => 'set-cookie',
    'Strict-Transport-Security' => 'strict-transport-security',
    'strict-transport-security' => 'strict-transport-security',
    'Transfer-Encoding' => 'transfer-encoding',
    'transfer-encoding' => 'transfer-encoding',
    'Upgrade-Insecure-Requests' => 'upgrade-insecure-requests',
    'upgrade-insecure-requests' => 'upgrade-insecure-requests',
    'User-Agent' => 'user-agent',
    'user-agent' => 'user-agent',
    'Vary' => 'vary',
    'vary' => 'vary',
    'X-Content-Type-Options' => 'x-content-type-options',
    'x-content-type-options' => 'x-content-type-options',
    'X-Forwarded-For' => 'x-forwarded-for',
    'x-forwarded-for' => 'x-forwarded-for',
    'X-Forwarded-Host' => 'x-forwarded-host',
    'x-forwarded-host' => 'x-forwarded-host',
    'X-Forwarded-Proto' => 'x-forwarded-proto',
    'x-forwarded-proto' => 'x-forwarded-proto',
    'X-Frame-Options' => 'x-frame-options',
    'x-frame-options' => 'x-frame-options',
    'X-Xss-Protection' => 'x-xss-protection',
    'x-xss-protection' => 'x-xss-protection',
];
