<?php

namespace OpenApi\Tests\Fixtures\InheritProperties;

/**
 * @OA\Schema()
 */
interface BaseInterface
{

    /**
     * @OA\Property(property="interfaceProperty");
     * @var string
     */
    public function getInterfaceProperty();
}
