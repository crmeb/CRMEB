<?php declare(strict_types=1);

namespace OpenApi\Tests\Fixtures\Parser;

use OpenApi\Tests\Fixtures\Parser\AsTrait as Aliased;

/**
 * @OA\Schema(schema="hello")
 */
trait HelloTrait
{
    use OtherTrait, Aliased;

    /**
     * @OA\Property()
     */
    public $greet = 'Hello!';
}
