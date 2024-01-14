<?php

namespace Amp\Http\StructuredFields;

/**
 * @template-covariant Inner of scalar|list<Item<scalar>>
 * @psalm-import-type Rfc8941Parameters from Rfc8941
 */
abstract class Item
{
    /**
     * @param Inner $item
     * @param Rfc8941Parameters $parameters
     */
    protected function __construct(public readonly int|float|string|bool|array $item, public readonly array $parameters)
    {
    }
}
