<?php

namespace PetstoreIO;

/**
 * @OA\Schema(@OA\Xml(name="Order"))
 */
class Order
{

    /**
     * @OA\Property(format="int64")
     * @var int
     */
    public $id;

    /**
     * @OA\Property(format="int64")
     * @var int
     */
    public $petId;

    /**
     * @OA\Property(default=false)
     * @var bool
     */
    public $complete;

    /**
     * @OA\Property(format="int32")
     * @var int
     */
    public $quantity;

    /**
     * @var \DateTime
     * @OA\Property()
     */
    public $shipDate;

    /**
     * Order Status
     * @var string
     * @OA\Property(enum={"placed", "approved", "delivered"})
     */
    public $status;
}
