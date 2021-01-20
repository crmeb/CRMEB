<?php

namespace OpenApi\Tests\Fixtures\InheritProperties;

/**
 * @OA\Schema()
 */
class BaseThatImplements implements BaseInterface
{

    /**
     * @OA\Property();
     * @var string
     */
    public $baseProperty;

    /**
     * {@inheritDoc}
     */
    public function getInterfaceProperty()
    {
        return "foo";
    }
}
