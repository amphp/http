<?php declare(strict_types=1);

namespace Amp\Http;

use League\Uri\Http;
use PHPUnit\Framework\TestCase;

class TestHttpRequest extends HttpRequest
{
    public function setQueryParameter(string $key, string $value): void
    {
        parent::setQueryParameter($key, $value);
    }

    public function addQueryParameter(string $key, string $value): void
    {
        parent::addQueryParameter($key, $value);
    }

    public function setQueryParameters(array $parameters): void
    {
        parent::setQueryParameters($parameters);
    }

    public function replaceQueryParameters(array $parameters): void
    {
        parent::replaceQueryParameters($parameters);
    }

    public function removeQueryParameters(string $key): void
    {
        parent::removeQueryParameters($key);
    }

    public function removeQuery(): void
    {
        parent::removeQuery();
    }
}

class HttpRequestTest extends TestCase
{
    public function createTestRequest(string $query, string $method = 'GET'): TestHttpRequest
    {
        $uri = Http::createFromComponents(['query' => $query]);
        return new TestHttpRequest($method, $uri);
    }

    public function testBasicQuery(): void
    {
        $request = $this->createTestRequest('key=value');
        self::assertTrue($request->hasQueryParameter('key'));
        self::assertSame('value', $request->getQueryParameter('key'));
    }

    public function testQueryWithMultiplePairs(): void
    {
        $request = $this->createTestRequest('key1=1.1&key1=1.2&key2=2.1&key2=2.2');

        self::assertTrue($request->hasQueryParameter('key1'));
        self::assertSame('1.1', $request->getQueryParameter('key1'));
        self::assertSame(['1.1', '1.2'], $request->getQueryParameterArray('key1'));

        self::assertTrue($request->hasQueryParameter('key2'));
        self::assertSame(['2.1', '2.2'], $request->getQueryParameterArray('key2'));
        self::assertSame(['key1' => ['1.1', '1.2'], 'key2' => ['2.1', '2.2']], $request->getQueryParameters());

        self::assertFalse($request->hasQueryParameter('key3'));

        $request->addQueryParameter('key1', '1.3');
        self::assertSame(['key1' => ['1.1', '1.2', '1.3'], 'key2' => ['2.1', '2.2']], $request->getQueryParameters());
    }

    public function testQueryWithArrayKeys(): void
    {
        $request = $this->createTestRequest('key1[]=1.1&key1[]=1.2&key2[]=2.1&key2[]=2.2');

        self::assertTrue($request->hasQueryParameter('key1[]'));
        self::assertSame('1.1', $request->getQueryParameter('key1[]'));
        self::assertSame(['1.1', '1.2'], $request->getQueryParameterArray('key1[]'));

        self::assertTrue($request->hasQueryParameter('key2[]'));
        self::assertSame(['2.1', '2.2'], $request->getQueryParameterArray('key2[]'));
        self::assertSame(['key1[]' => ['1.1', '1.2'], 'key2[]' => ['2.1', '2.2']], $request->getQueryParameters());

        $request->addQueryParameter('key1[]', '1.3');
        self::assertSame(['key1[]' => ['1.1', '1.2', '1.3'], 'key2[]' => ['2.1', '2.2']], $request->getQueryParameters());
    }

    public function testQueryWithEmptyPairs(): void
    {
        $request = $this->createTestRequest('&&&=to&&key=value');

        self::assertSame([
            '' => [null, null, null, 'to', null],
            'key' => ['value'],
        ], $request->getQueryParameters());
    }
}
