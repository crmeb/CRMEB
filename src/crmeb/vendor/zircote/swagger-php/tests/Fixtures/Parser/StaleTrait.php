<?php declare(strict_types=1);

namespace OpenApi\Tests\Fixtures\Parser;

/**
 * @OA\Schema(schema="stale")
 */
trait StaleTrait
{

    /**
     * @OA\Property()
     */
    public $expired = true;
}
