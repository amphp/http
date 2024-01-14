<?php

namespace Amp\Http\StructuredFields;

/**
 * @psalm-import-type Rfc8941Parameters from \Amp\Http\StructuredFields\Rfc8941
 * @template-extends Item<bool>
 */
class Boolean extends Item
{
    /**
     * @psalm-param Rfc8941Parameters $parameters
     */
    public function __construct(bool $item, array $parameters)
    {
        parent::__construct($item, $parameters);
    }
}
