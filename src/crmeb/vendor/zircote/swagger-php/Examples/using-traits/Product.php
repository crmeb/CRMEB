<?php

namespace UsingTraits;

/**
 * @OA\Schema(title="Product model")
 */
class Product {
    use \UsingTraits\Colour;
    use BellsAndWhistles;

    /**
     * The unique identifier of a product in our catalog.
     *
     * @var integer
     * @OA\Property(format="int64", example=1)
     */
    public $id;

    /**
     * The product bell.
     *
     * @OA\Property(example="gong")
     */
    public $bell;
}
