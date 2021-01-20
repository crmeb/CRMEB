<?php

/**
 * @license Apache 2.0
 */

namespace Petstore30;

/**
 * Class Pet
 *
 * @package Petstore30
 *
 * @author  Donii Sergii <doniysa@gmail.com>
 *
 * @OA\Schema(
 *     description="Pet model",
 *     title="Pet model",
 *     required={"name", "photoUrls"},
 *     @OA\Xml(
 *         name="Pet"
 *     )
 * )
 */
class Pet
{

    /**
     * @OA\Property(
     *     format="int64",
     *     description="ID",
     *     title="ID",
     * )
     *
     * @var integer
     */
    private $id;

    /**
     * @OA\Property(
     *     description="Category relation",
     *     title="Category",
     * )
     *
     * @var \Petstore30\Category
     */
    private $category;

    /**
     * @OA\Property(
     *     format="int64",
     *     description="Pet name",
     *     title="Pet name",
     * )
     *
     * @var integer
     */
    private $name;

    /**
     * @OA\Property(
     *     description="Photo urls",
     *     title="Photo urls",
     *     @OA\Xml(
     *         name="photoUrl",
     *         wrapped=true
     *     ),
     *     @OA\Items(
     *         type="string",
     *         default="images/image-1.png"
     *     )
     * )
     *
     * @var array
     */
    private $photoUrls;

    /**
     * @OA\Property(
     *     description="Pet tags",
     *     title="Pet tags",
     *     @OA\Xml(
     *         name="tag",
     *         wrapped=true
     *     ),
     * )
     *
     * @var \Petstore30\Tag[]
     */
    private $tags;
}
