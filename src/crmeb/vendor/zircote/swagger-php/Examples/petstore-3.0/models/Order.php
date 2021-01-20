<?php

/**
 * @license Apache 2.0
 */

namespace Petstore30;

/**
 * Class Order
 *
 * @package Petstore30
 *
 * @author  Donii Sergii <doniysa@gmail.com>
 * @OA\Schema(
 *     title="Order model",
 *     description="Order model",
 * )
 */
class Order
{
    /**
     * @OA\Property(
     *     format="int64",
     *     title="ID",
     *     default=1,
     *     description="ID",
     * )
     *
     * @var integer
     */
    private $id;

    /**
     * @OA\Property(
     *     default=1,
     *     format="int64",
     *     description="Pet ID",
     *     title="Pet ID",
     * )
     *
     * @var integer
     */
    private $petId;

    /**
     * @OA\Property(
     *     default=12,
     *     format="in32",
     *     description="Quantity",
     *     title="Quantity",
     * )
     *
     * @var integer
     */
    private $quantity;

    /**
     * @OA\Property(
     *     default="2017-02-02 18:31:45",
     *     format="datetime",
     *     description="Shipping date",
     *     title="Shipping date",
     *     type="string"
     * )
     *
     * @var \DateTime
     */
    private $shipDate;

    /**
     * @OA\Property(
     *     default="placed",
     *     title="Order status",
     *     description="Order status",
     *     enum={"placed", "approved", "delivered"},
     * )
     *
     * @var string
     */
    private $status;

    /**
     * @OA\Property(
     *     default="false",
     *     format="int64",
     *     description="Complete status",
     *     title="Complete status",
     * )
     *
     * @var boolean
     */
    private $complete;
}
