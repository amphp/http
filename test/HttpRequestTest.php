<?php declare(strict_types=1);

namespace Amp\Http;

use League\Uri\Http;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface as PsrUri;

class TestHttpRequest extends HttpRequest
{
    public function setMethod(string $method): void
    {
        parent::setMethod($method);
    }

    public function setUri(PsrUri $uri): void
    {
        parent::setUri($uri);
    }

    public function setQueryParameter(string $key, array|string|null $value): void
    {
        parent::setQueryParameter($key, $value);
    }

    public function addQueryParameter(string $key, array|string|null $value): void
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

    public function removeQueryParameter(string $key): void
    {
        parent::removeQueryParameter($key);
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

    public function testMethod(): void
    {
        $request = $this->createTestRequest('', 'PUT');
        self::assertSame('PUT', $request->getMethod());

        $request->setMethod('POST');
        self::assertSame('POST', $request->getMethod());
    }

    public function testBasicQuery(): void
    {
        $request = $this->createTestRequest('key=value');
        self::assertTrue($request->hasQueryParameter('key'));
        self::assertSame('value', $request->getQueryParameter('key'));
    }

    public function testQueryWithMissingKey(): void
    {
        $query = 'key=value';
        $request = $this->createTestRequest($query);
        self::assertNull($request->getQueryParameter('value'));
        self::assertSame('value', $request->getQueryParameter('key'));
        self::assertSame($query, $request->getUri()->getQuery());
    }

    public function testQueryWithEmptyValues(): void
    {
        $query = 'key1&key2&key3=3.1';
        $request = $this->createTestRequest($query);
        self::assertTrue($request->hasQueryParameter('key1'));
        self::assertSame('', $request->getQueryParameter('key1'));

        self::assertTrue($request->hasQueryParameter('key2'));
        self::assertSame('', $request->getQueryParameter('key2'));

        self::assertTrue($request->hasQueryParameter('key3'));
        self::assertSame('3.1', $request->getQueryParameter('key3'));
        self::assertSame($query, $request->getUri()->getQuery());

        self::assertFalse($request->hasQueryParameter('key4'));
        self::assertNull($request->getQueryParameter('key4'));
    }

    public function testQueryWithEncodedChars(): void
    {
        $query = 'key%5B1%5D=1%201&key%5B2%5D=2%261&key%5B3%5D=3%5B1%5D';
        $request = $this->createTestRequest($query);
        self::assertSame('1 1', $request->getQueryParameter('key[1]'));
        self::assertSame('2&1', $request->getQueryParameter('key[2]'));
        self::assertSame('3[1]', $request->getQueryParameter('key[3]'));

        $request->setQueryParameter('key[3]', '3[2]');
        self::assertSame(\str_replace('3%5B1%5D', '3%5B2%5D', $query), $request->getUri()->getQuery());
    }

    public function testQueryWithMultipleKeyValues(): void
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

        $request->replaceQueryParameters(['key1' => '1.4']);
        self::assertSame(['key1' => ['1.4'], 'key2' => ['2.1', '2.2']], $request->getQueryParameters());
        self::assertSame('key1=1.4&key2=2.1&key2=2.2', $request->getUri()->getQuery());

        $request->removeQueryParameter('key2');
        self::assertSame(['key1' => ['1.4']], $request->getQueryParameters());
        self::assertSame('key1=1.4', $request->getUri()->getQuery());

        $request->setQueryParameters(['key2' => '2.3']);
        self::assertSame(['key2' => ['2.3']], $request->getQueryParameters());
        self::assertSame('key2=2.3', $request->getUri()->getQuery());
    }

    public function testQueryWithArrayKeys(): void
    {
        $request = $this->createTestRequest('key[0]=0&key[1]=1&key[2]=2&key[3]=3');
        self::assertSame(
            ['key[0]' => ['0'], 'key[1]' => ['1'], 'key[2]' => ['2'], 'key[3]' => ['3']],
            $request->getQueryParameters(),
        );
    }

    public function testQueryWithIntegerKeys(): void
    {
        $request = $this->createTestRequest('0=0&1=1&2=2');
        self::assertSame([0 => ['0'], 1 => ['1'], 2 => ['2']], $request->getQueryParameters());
        self::assertSame('0', $request->getQueryParameter('0'));
        self::assertSame('1', $request->getQueryParameter('1'));
        self::assertSame('2', $request->getQueryParameter('2'));

        $request->setQueryParameters(['a', 'b']);
        self::assertSame('0=a&1=b', $request->getUri()->getQuery());
    }

    public function testEmptyQuery(): void
    {
        $request = $this->createTestRequest('');
        self::assertSame([], $request->getQueryParameters());
    }

    public function testRemoveQuery(): void
    {
        $query = 'key=value';
        $request = $this->createTestRequest($query);
        self::assertSame($query, $request->getUri()->getQuery());

        $request->removeQuery();
        self::assertSame([], $request->getQueryParameters());
        self::assertSame('', $request->getUri()->getQuery());
    }

    public function testQueryWithEmptyPairs(): void
    {
        $request = $this->createTestRequest('&&&=to&&key=value&empty');

        self::assertSame([
            '' => ['', '', '', 'to', ''],
            'key' => ['value'],
            'empty' => [''],
        ], $request->getQueryParameters());

        self::assertTrue($request->hasQueryParameter(''));
        self::assertSame('', $request->getQueryParameter(''));
        self::assertTrue($request->hasQueryParameter('empty'));
        self::assertSame('', $request->getQueryParameter('empty'));

        $request->setQueryParameter('key', 'test');
        self::assertSame('test', $request->getQueryParameter('key'));
        self::assertSame('&&&=to&&key=test&empty', $request->getUri()->getQuery());
    }

    public function testSetUri(): void
    {
        $request = $this->createTestRequest('key1=value1');
        self::assertSame('value1', $request->getQueryParameter('key1'));

        $request->setUri(Http::createFromComponents(['query' => 'key2=value2']));
        self::assertNull($request->getQueryParameter('key1'));
        self::assertSame('value2', $request->getQueryParameter('key2'));
    }

    public function testSettingNonStringValues(): void
    {
        $request = $this->createTestRequest('');
        $request->setQueryParameter('key1', [1]);
        $request->setQueryParameter('key2', [3.14]);
        $request->setQueryParameter('key3', [1, 2, 3]);

        self::assertSame('1', $request->getQueryParameter('key1'));
        self::assertSame('3.14', $request->getQueryParameter('key2'));
        self::assertSame(['1', '2', '3'], $request->getQueryParameterArray('key3'));

        self::assertSame('key1=1&key2=3.14&key3=1&key3=2&key3=3', $request->getUri()->getQuery());

        $this->expectException(\TypeError::class);
        $request->setQueryParameter('key4', [true]);
    }

    public function testIsIdempotent(): void
    {
        self::assertTrue($this->createTestRequest('', 'HEAD')->isIdempotent());
        self::assertFalse($this->createTestRequest('', 'POST')->isIdempotent());
    }
}
