<?php declare(strict_types=1);

namespace AnotherNamespace;

/**
 * @OA\Schema()
 */
class ChildWithDocBlocks extends \OpenApi\Tests\Fixtures\AncestorWithoutDocBlocks
{

    /**
     * @var bool
     * @OA\Property()
     */
    public $isBaby;
}
