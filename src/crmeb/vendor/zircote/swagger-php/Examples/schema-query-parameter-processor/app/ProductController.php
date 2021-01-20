<?php

namespace App;

/**
 * A controller.
 */
class ProductController
{

    /**
     * @OA\Get(
     *   tags={"Products"},
     *   path="/products/{id}",
     *   @OA\Response(
     *     response=200,
     *     description="A single product",
     *     @OA\JsonContent(ref="#/components/schemas/Product")
     *   )
     * )
     */
    public function getProduct($id)
    {
    }

    /**
     * Controller that takes all `Product` properties as query parameter.
     *
     * @OA\Get(
     *   tags={"Products"},
     *   path="/products/search",
     *   x={"query-args-$ref"="#/components/schemas/Product"},
     *   @OA\Response(
     *       response=200,
     *       description="A list of matching products",
     *       @OA\JsonContent(
     *           type="array",
     *           @OA\Items(ref="#/components/schemas/Product")
     *       )
     *   )
     * )
     */
    public function findProducts($id)
    {
    }
}
