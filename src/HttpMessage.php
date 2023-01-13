<?php declare(strict_types=1);

namespace Amp\Http;

/**
 * Base class for HTTP request and response messages.
 */
abstract class HttpMessage
{
    public const HEADER_LOWER = [
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
        'Cookie' => 'cookie',
        'cookie' => 'cookie',
        'Date' => 'date',
        'date' => 'date',
        'Host' => 'host',
        'host' => 'host',
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
        'Upgrade-Insecure-Requests' => 'upgrade-insecure-requests',
        'upgrade-insecure-requests' => 'upgrade-insecure-requests',
        'User-Agent' => 'user-agent',
        'user-agent' => 'user-agent',
        'Vary' => 'vary',
        'vary' => 'vary',
    ];

    /** @var array<string, list<string>> */
    private array $headers = [];

    /** @var array<string, list<string>> */
    private array $headerCase = [];

    /**
     * Returns the headers as a string-indexed array of arrays of strings or an empty array if no headers
     * have been set.
     *
     * @return array<string, list<string>>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Returns the headers as list of [field, name] pairs in the original casing provided by the application or server.
     */
    final public function getRawHeaders(): array
    {
        $headers = [];

        foreach ($this->headers as $lcName => $values) {
            $size = \count($values);

            for ($i = 0; $i < $size; $i++) {
                $headers[] = [$this->headerCase[$lcName][$i], $values[$i]];
            }
        }

        return $headers;
    }

    /**
     * Returns the array of values for the given header or an empty array if the header does not exist.
     *
     * @return list<string>
     */
    public function getHeaderArray(string $name): array
    {
        return $this->headers[self::HEADER_LOWER[$name] ?? \strtolower($name)] ?? [];
    }

    /**
     * Returns the value of the given header. If multiple headers are present for the named header, only the first
     * header value will be returned. Use getHeaderArray() to return an array of all values for the particular header.
     * Returns null if the header does not exist.
     */
    public function getHeader(string $name): ?string
    {
        return $this->headers[self::HEADER_LOWER[$name] ?? \strtolower($name)][0] ?? null;
    }

    /**
     * Removes all current headers and sets new headers from the given array.
     *
     * @param array<string, string|array<string>> $headers
     */
    protected function setHeaders(array $headers): void
    {
        // Ensure this is an atomic operation, either all headers are set or none.
        $before = $this->headers;
        $beforeCase = $this->headerCase;

        $this->headers = [];
        $this->headerCase = [];

        try {
            foreach ($headers as $name => $value) {
                $this->setHeader($name, $value);
            }
        } catch (\Throwable $e) {
            $this->headers = $before;
            $this->headerCase = $beforeCase;

            throw $e;
        }
    }

    /**
     * Replaces headers from the given array. Header names not contained in the array are not changed.
     *
     * @param array<string, string|array<string>> $headers
     */
    protected function replaceHeaders(array $headers): void
    {
        // Ensure this is an atomic operation, either all given headers are replaced or none.
        $before = $this->headers;
        $beforeCase = $this->headerCase;

        try {
            foreach ($headers as $name => $value) {
                $this->setHeader($name, $value);
            }
        } catch (\Throwable $e) {
            $this->headers = $before;
            $this->headerCase = $beforeCase;

            throw $e;
        }
    }

    /**
     * Sets the named header to the given value.
     *
     * @param string|array<string> $value
     *
     * @throws \Error If the header name or value is invalid.
     */
    protected function setHeader(string $name, array|string $value): void
    {
        \assert($this->isNameValid($name), "Invalid header name");

        $lcName = self::HEADER_LOWER[$name] ?? \strtolower($name);

        if (!\is_array($value)) {
            \assert($this->isValueValid([$value]), "Invalid header value");
            $this->headers[$lcName] = [$value];
            $this->headerCase[$lcName] = [$name];
            return;
        }

        if (!$value) {
            $this->removeHeader($name);
            return;
        }

        $value = \array_values(\array_map(\strval(...), $value));

        \assert($this->isValueValid($value), "Invalid header value");

        $this->headers[$lcName] = $value;
        $this->headerCase[$lcName] = \array_fill(0, \count($value), $name);
    }

    /**
     * Adds the value to the named header, or creates the header with the given value if it did not exist.
     *
     * @param string|array<string> $value
     *
     * @throws \Error If the header name or value is invalid.
     */
    protected function addHeader(string $name, array|string $value): void
    {
        \assert($this->isNameValid($name), "Invalid header name");

        $lcName = self::HEADER_LOWER[$name] ?? \strtolower($name);

        if (!\is_array($value)) {
            \assert($this->isValueValid([$value]), "Invalid header value");
            $this->headers[$lcName][] = $value;
            $this->headerCase[$lcName][] = $name;
            return;
        }

        $value = \array_values(\array_map(\strval(...), $value));

        \assert($this->isValueValid($value), "Invalid header value");

        foreach ($value as $header) {
            $this->headers[$lcName][] = $header;
            $this->headerCase[$lcName][] = $name;
        }
    }

    /**
     * Removes the given header if it exists.
     */
    protected function removeHeader(string $name): void
    {
        $lcName = self::HEADER_LOWER[$name] ?? \strtolower($name);

        unset($this->headers[$lcName], $this->headerCase[$lcName]);
    }

    /**
     * Checks if given header exists.
     */
    public function hasHeader(string $name): bool
    {
        return isset($this->headers[self::HEADER_LOWER[$name] ?? \strtolower($name)]);
    }

    private function isNameValid(string $name): bool
    {
        return (bool) \preg_match('/^[A-Za-z0-9`~!#$%^&_|\'\-:]+$/', $name);
    }

    /**
     * Determines if the given value is a valid header value.
     *
     * @param list<string> $values
     *
     * @throws \Error If the given value cannot be converted to a string and is not an array of values that can be
     *     converted to strings.
     */
    private function isValueValid(array $values): bool
    {
        foreach ($values as $value) {
            if (\preg_match("/[^\t\r\n\x20-\x7e\x80-\xfe]|\r\n/", $value)) {
                return false;
            }
        }

        return true;
    }
}
