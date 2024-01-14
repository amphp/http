<?php

namespace Amp\Http\StructuredFields;

/**
 * @psalm-import-type Rfc8941Parameters from \Amp\Http\StructuredFields\Rfc8941
 * @template-extends Item<int>
 */
class Date extends Item
{
    /**
     * @psalm-param Rfc8941Parameters $parameters
     */
    public function __construct(int $item, array $parameters)
    {
        parent::__construct($item, $parameters);
    }
}
