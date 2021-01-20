<?php

namespace App;

/**
 * @OA\Schema(
 *     title="Product",
 *     description="A simple product model"
 * )
 */
class Product
{

    /**
     * The unique identifier of a product in our catalog.
     *
     * @var integer
     * @OA\Property(format="int64", example=1)
     */
    public $id;

    /**
     * @var string
     * @OA\Property(format="int64", example=1)
     */
    public $name;
}
