<?php declare(strict_types=1);

namespace OpenApi\Tests\Fixtures\Parser;

/**
 * @OA\Schema(schema="as")
 */
trait AsTrait
{
    use OtherTrait;
}
