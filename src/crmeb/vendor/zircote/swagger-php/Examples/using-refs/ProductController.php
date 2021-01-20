<?php
namespace UsingRefs;

/**
 * @OA\PathItem(
 *   path="/products/{product_id}",
 *   @OA\Parameter(ref="#/components/parameters/product_id_in_path_required")
 * )
 */

class ProductController
{

    /**
     * @OA\Get(
     *   tags={"Products"},
     *   path="/products/{product_id}",
     *   @OA\Response(
     *       response="default",
     *       description="successful operation",
     *       @OA\JsonContent(ref="#/components/responses/product")
     *   )
     * )
     */
    public function getProduct($id)
    {
    }

    /**
     * @OA\Patch(
     *   tags={"Products"},
     *   path="/products/{product_id}",
     *   @OA\RequestBody(ref="#/components/requestBodies/product_in_body"),
     *   @OA\Response(
     *       response="default",
     *       description="successful operation",
     *       @OA\JsonContent(ref="#/components/responses/product")
     *   )
     * )
     */
    public function updateProduct($id)
    {
    }

    /**
     * @OA\Post(
     *   tags={"Products"},
     *   path="/products",
     *   @OA\Parameter(ref="#/components/requestBodies/product_in_body"),
     *   @OA\Response(
     *       response="default",
     *       description="successful operation",
     *       @OA\JsonContent(ref="#/components/responses/product")
     *   )
     * )
     */
    public function addProduct($id)
    {
    }
}
