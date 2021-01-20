<?php

namespace UsingTraits;

/**
 * A controller.
 */
class ProductController
{
    use DeleteEntity;

    /**
     * @OA\Get(
     *   tags={"Products"},
     *   path="/products/{product_id}",
     *   @OA\Parameter(
     *     description="ID of product to return",
     *     in="path",
     *     name="product_id",
     *     required=true,
     *     @OA\Schema(
     *       type="string"
     *     )
     *   ),
     *   @OA\Response(
     *       response="default",
     *       description="successful operation"
     *   )
     * )
     */
    public function getProduct($id)
    {
    }
}
