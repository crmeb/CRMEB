<?php declare(strict_types=1);

namespace AnotherNamespace;

/**
 * @OA\Schema()
 */
class Child extends \OpenApi\Tests\Fixtures\Ancestor
{

    /**
     * @var bool
     * @OA\Property()
     */
    public $isBaby;
}
