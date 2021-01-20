<?php

/**
 * @license Apache 2.0
 */

namespace Petstore30\controllers;

/**
 * Class Store
 *
 * @package Petstore30\controllers
 *
 * @author  Donii Sergii <doniysa@gmail.com>
 */
class Store
{
    /**
     * @OA\Get(
     *     path="/store",
     *     tags={"store"},
     *     summary="Returns pet inventories by status",
     *     description="Returns a map of status codes to quantities",
     *     operationId="getInventory",
     *     @OA\Response(
     *         response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              @OA\AdditionalProperties(
     *                  type="integer",
     *                  format="int32"
     *              )
     *          )
     *     ),
     *     security={
     *         {"api_key": {}}
     *     }
     * )
     */
    public function getInventory()
    {
    }

    /**
     * @OA\Post(
     *     path="/store/order",
     *     tags={"store"},
     *     summary="Place an order for a pet",
     *     operationId="placeOrder",
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Order"),
     *         @OA\XmlContent(ref="#/components/schemas/Order")
     *     ),
     *     @OA\RequestBody(
     *         description="order placed for purchasing th pet",
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Order")
     *     )
     * )
     */
    public function placeOrder()
    {
    }

    /**
     * @OA\Get(
     *     path="/store/order/{orderId}",
     *     tags={"store"},
     *     description="For valid response try integer IDs with value >= 1 and <= 10. Other values will generated exceptions",
     *     operationId="getOrderById",
     *     @OA\Parameter(
     *         name="orderId",
     *         in="path",
     *         description="ID of pet that needs to be fetched",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64",
     *             maximum=1,
     *             minimum=10
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Order"),
     *         @OA\MediaType(
     *             mediaType="application/xml",
     *             @OA\Schema(ref="#/components/schemas/Order")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID supplied"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found"
     *     )
     * )
     */
    public function getOrderById()
    {
    }

    /**
     * @OA\Delete(
     *     path="/store/order/{orderId}",
     *     tags={"store"},
     *     summary="Delete purchase order by ID",
     *     description="For valid response try integer IDs with positive integer value. Negative or non-integer values will generate API errors",
     *     operationId="deleteOrder",
     *     @OA\Parameter(
     *         name="orderId",
     *         in="path",
     *         required=true,
     *         description="ID of the order that needs to be deleted",
     *         @OA\Schema(
     *             type="integer",
     *             format="int64",
     *             minimum=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID supplied"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found"
     *     )
     * ),
     */
    public function deleteOrder()
    {
    }
}
