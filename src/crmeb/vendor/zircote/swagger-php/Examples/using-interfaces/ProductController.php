<?php

namespace UsingInterfaces;

class ProductController
{

    /**
     * @OA\Get(
     *   tags={"Products"},
     *   path="/products/{id}",
     *   @OA\Parameter(
     *     description="ID of product to return",
     *     in="path",
     *     name="id",
     *     required=true,
     *     @OA\Schema(
     *       type="string"
     *     )
     *   ),
     *   @OA\Response(
     *       response="default",
     *       description="successful operation",
     *       @OA\JsonContent(ref="#/components/schemas/Product")
     *   )
     * )
     */
    public function getProduct($id)
    {
    }
}
