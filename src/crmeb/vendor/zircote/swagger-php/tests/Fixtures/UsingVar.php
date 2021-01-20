<?php declare(strict_types=1);

namespace OpenApi\Tests\Fixtures;

/**
 * @OA\Schema(
 *   schema="UsingVar",
 *   required={"name"}
 * )
 */
class UsingVar
{
    /**
     * @var string
     * @OA\Property
     */
    private $name;

    /**
     * @var \DateTimeInterface
     * @OA\Property(ref="#/components/schemas/date")
     */
    private $createdAt;
}

/**
 * @OA\Schema(
 *   schema="date",
 *   type="datetime"
 * )
 */
