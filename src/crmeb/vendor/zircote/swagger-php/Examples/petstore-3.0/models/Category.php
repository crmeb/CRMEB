<?php

/**
 * @license Apache 2.0
 */

namespace Petstore30;

/**
 * Class Category
 *
 * @package Petstore30
 *
 * @author  Donii Sergii <doniysa@gmail.com>
 *
 * @OA\Schema(
 *     description="Pets Category",
 *     title="Pets Category",
 *     @OA\Xml(
 *         name="Category"
 *     )
 * )
 */
class Category
{
    /**
     * @OA\Property(
     *     title="ID",
     *     description="ID",
     *     format="int64",
     * )
     *
     * @var integer
     */
    private $id;

    /**
     * @OA\Property(
     *     title="Category name",
     *     description="Category name"
     * )
     *
     * @var string
     */
    private $name;
}
