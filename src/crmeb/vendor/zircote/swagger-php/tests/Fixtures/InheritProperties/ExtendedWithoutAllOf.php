<?php

namespace OpenApi\Tests\Fixtures;

/**
 * @OA\Schema()
 */
class ExtendedWithoutAllOf extends Base
{

    /**
     * @OA\Property();
     * @var string
     */
    public $extendedProperty;
}
