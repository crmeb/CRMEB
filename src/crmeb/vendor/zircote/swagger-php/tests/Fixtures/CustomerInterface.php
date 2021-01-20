<?php declare(strict_types=1);

namespace OpenApi\Tests\Fixtures;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="Customer", description="Fixture for Interface Test")
 */
interface CustomerInterface
{
    /**
     * The first name of the customer.
     *
     * @var string
     * @example John
     * @OA\Property()
     */
    public function firstname();

    /**
     * @var null|string The second name of the customer.
     * @example Allan
     * @OA\Property()
     */
    public function secondname();

    /**
     * The third name of the customer.
     *
     * @var string|null
     * @example Peter
     * @OA\Property()
     */
    public function thirdname();

    /**
     * The unknown name of the customer.
     *
     * @var unknown|null
     * @example Unknown
     * @OA\Property()
     */
    public function fourthname();

    /**
     * @var string The lastname of the customer.
     * @OA\Property()
     */
    public function lastname();

    /**
     * @OA\Property()
     * @var string[]
     */
    public function tags();

    /**
     * @OA\Property()
     * @var Customer
     */
    public function submittedBy();

    /**
     * @OA\Property()
     * @var Customer[]
     */
    public function friends();

    /**
     * @OA\Property()
     * @var Customer|null
     */
    public function bestFriend();
}
