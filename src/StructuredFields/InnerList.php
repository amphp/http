<?php

namespace Amp\Http\StructuredFields;

/**
 * @psalm-import-type Rfc8941Parameters from Rfc8941
 * @extends Item<list<Item<scalar>>>
 */
final class InnerList extends Item
{
    /**
     * @psalm-param list<Item<scalar>> $item
     * @psalm-param Rfc8941Parameters $parameters
     */
    public function __construct(array $item, array $parameters)
    {
        parent::__construct($item, $parameters);
    }
}
