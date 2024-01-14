<?php declare(strict_types=1);

namespace Amp\Http\StructuredFields;

/**
 * @psalm-import-type Rfc8941Parameters from Rfc8941
 * @extends Item<int>
 */
final class Date extends Item
{
    /**
     * @param Rfc8941Parameters $parameters
     */
    public function __construct(int $item, array $parameters)
    {
        parent::__construct($item, $parameters);
    }
}
