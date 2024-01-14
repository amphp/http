<?php

namespace Amp\Http\StructuredFields;

/**
 * @psalm-import-type Rfc8941Parameters from Rfc8941
 * @extends Item<int|float>
 */
final class Number extends Item
{
    /**
     * @param Rfc8941Parameters $parameters
     */
    public function __construct(int|float $item, array $parameters)
    {
        parent::__construct($item, $parameters);
    }
}
