<?php declare(strict_types=1);

namespace OpenApi\Tests\Fixtures;

/**
 * @OA\Schema()
 */
class NestedProperty
{

    /**
     * @var bool
     * @OA\Property(
     *     @OA\Property(
     *         property="babyProperty",
     *         @OA\Property(
     *             property="theBabyOfBaby",
     *             properties={@OpenApi\Annotations\Property(type="string", property="theBabyOfBabyBaby")}
     *         )
     *     ),
     * )
     */
    public $parentProperty;
}
