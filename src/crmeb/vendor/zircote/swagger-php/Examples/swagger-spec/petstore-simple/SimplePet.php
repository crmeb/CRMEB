<?php

namespace Petstore;

/**
 * @OA\Schema(schema="NewPet", required={"name"})
 */
class SimplePet
{
    public $id;

    /**
     * @OA\Property()
     * @var string
     */
    public $name;

    /**
     * @var string
     * @OA\Property()
     */
    public $tag;
}

/**
 *  @OA\Schema(
 *   schema="Pet",
 *   type="object",
 *   allOf={
 *       @OA\Schema(ref="#/components/schemas/NewPet"),
 *       @OA\Schema(
 *           required={"id"},
 *           @OA\Property(property="id", format="int64", type="integer")
 *       )
 *   }
 * )
 */
