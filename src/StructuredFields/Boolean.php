<?php declare(strict_types=1);

namespace Amp\Http\StructuredFields;

/**
 * @psalm-import-type Rfc8941Parameters from Rfc8941
 * @extends Item<bool>
 */
final class Boolean extends Item
{
    /**
     * @param Rfc8941Parameters $parameters
     */
    public function __construct(bool $item, array $parameters)
    {
        parent::__construct($item, $parameters);
    }
}
