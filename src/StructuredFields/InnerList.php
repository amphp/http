<?php

namespace Amp\Http\StructuredFields;

/**
 * @psalm-import-type Rfc8941Parameters from \Amp\Http\StructuredFields\Rfc8941
 * @psalm-import-type Rfc8941BareItem from \Amp\Http\StructuredFields\Rfc8941
 * @template-extends Item<list<Item<Rfc8941BareItem>>>
 */
class InnerList extends Item
{
    /**
     * @psalm-param list<Item<Rfc8941BareItem>> $item
     * @psalm-param Rfc8941Parameters $parameters
     */
    public function __construct(array $item, array $parameters)
    {
        parent::__construct($item, $parameters);
    }
}
