<?php

/**
 * @OA\Schema(
 *   schema="NewPet",
 *   required={"name"}
 * )
 */
class Pet
{
    public $id;
    /**
     * @OA\Property(type="string")
     */
    public $name;

    /**
     * @OA\Property(type="string")
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
