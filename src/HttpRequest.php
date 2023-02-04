<?php declare(strict_types=1);

namespace Amp\Http;

use League\Uri\Components\Query as QueryComponent;
use League\Uri\Contracts\QueryInterface as QueryContract;
use Psr\Http\Message\UriInterface as PsrUri;

abstract class HttpRequest extends HttpMessage
{
    private ?QueryContract $query = null;

    /**
     * @param non-empty-string $method
     * @param PsrUri $uri
     */
    public function __construct(
        private string $method,
        private PsrUri $uri,
    ) {
    }

    /**
     * @return non-empty-string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @param non-empty-string $method
     */
    protected function setMethod(string $method): void
    {
        $this->method = $method;
    }

    public function getUri(): PsrUri
    {
        return $this->uri;
    }

    protected function setUri(PsrUri $uri): void
    {
        $this->uri = $uri;
        $this->query = null;
    }

    public function hasQueryParameter(string $key): bool
    {
        return $this->getQuery()->has($key);
    }

    public function getQueryParameter(string $key): ?string
    {
        return $this->getQuery()->get($key);
    }

    /**
     * @return list<string|null>
     */
    public function getQueryParameterArray(string $key): array
    {
        return \array_values($this->getQuery()->getAll($key));
    }

    /**
     * @return array<string, list<string|null>>
     */
    public function getQueryParameters(): array
    {
        $parameters = [];
        foreach ($this->getQuery()->pairs() as $key => $value) {
            $parameters[$key] ??= [];
            $parameters[$key][] = $value;
        }

        return $parameters;
    }

    protected function setQueryParameter(string $key, string $value): void
    {
        $this->updateUriWithQuery($this->getQuery()->withPair($key, $value));
    }

    protected function addQueryParameter(string $key, string $value): void
    {
        $this->updateUriWithQuery($this->getQuery()->appendTo($key, $value));
    }

    /**
     * @param array<string, string|array<string>> $parameters
     */
    protected function setQueryParameters(array $parameters): void
    {
        $this->updateUriWithQuery(QueryComponent::createFromParams($parameters));
    }

    /**
     * @param array<string, string|array<string>> $parameters
     */
    protected function replaceQueryParameters(array $parameters): void
    {
        $query = QueryComponent::createFromParams($parameters);
        $this->updateUriWithQuery($this->getQuery()->merge($query->toRFC3986() ?? ''));
    }

    protected function removeQueryParameters(string $key): void
    {
        $this->updateUriWithQuery($this->getQuery()->withoutPair($key));
    }

    protected function removeQuery(): void
    {
        $this->setUri($this->uri->withQuery(''));
    }

    private function getQuery(): QueryContract
    {
        return $this->query ??= QueryComponent::createFromUri($this->uri);
    }

    private function updateUriWithQuery(QueryContract $query): void
    {
        $this->setUri($this->uri->withQuery($query->toRFC3986() ?? ''));
    }
}
