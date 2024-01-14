<?php

namespace Amp\Http\StructuredFields;

/**
 * @template-covariant Inner
 * @psalm-import-type Rfc8941Parameters from \Amp\Http\StructuredFields\Rfc8941
 */
class Item
{
    /**
     * @psalm-param Inner $item
     * @psalm-param Rfc8941Parameters $parameters
     */
    protected function __construct(public readonly int|float|string|bool|array $item, public readonly array $parameters)
    {
    }
}
