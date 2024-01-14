<?php declare(strict_types=1);

namespace Amp\Http\StructuredFields;

/**
 * @psalm-import-type Rfc8941Parameters from Rfc8941
 * @extends Item<string>
 */
final class Str extends Item
{
    /**
     * @param Rfc8941Parameters $parameters
     */
    public function __construct(string $item, array $parameters)
    {
        parent::__construct($item, $parameters);
    }
}
