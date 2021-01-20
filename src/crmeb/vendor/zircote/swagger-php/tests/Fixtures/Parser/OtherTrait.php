<?php declare(strict_types=1);

namespace OpenApi\Tests\Fixtures\Parser;

/**
 * @OA\Schema(schema="other")
 */
trait OtherTrait
{

    /**
     * @OA\Property()
     */
    public $so = 'what?';
}
