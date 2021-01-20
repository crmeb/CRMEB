<?php

namespace PetstoreIO;

abstract class StoreController
{

    /**
     * @OA\Get(path="/store/inventory",
     *   tags={"store"},
     *   summary="Returns pet inventories by status",
     *   description="Returns a map of status codes to quantities",
     *   operationId="getInventory",
     *   parameters={},
     *   @OA\Response(
     *     response=200,
     *     description="successful operation",
     *     @OA\Schema(
     *       additionalProperties={
     *         "type":"integer",
     *         "format":"int32"
     *       }
     *     )
     *   ),
     *   security={{
     *     "api_key":{}
     *   }}
     * )
     */
    public function getInventory()
    {
    }

    /**
     * @OA\Post(path="/store/order",
     *   tags={"store"},
     *   summary="Place an order for a pet",
     *   description="",
     *   operationId="placeOrder",
     *   @OA\RequestBody(
     *       required=true,
     *       description="order placed for purchasing the pet",
     *       @OA\JsonContent(ref="#/components/schemas/Order")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="successful operation",
     *     @OA\Schema(ref="#/components/schemas/Order")
     *   ),
     *   @OA\Response(response=400, description="Invalid Order")
     * )
     */
    public function placeOrder()
    {
    }

    /**
     * @OA\Get(path="/store/order/{orderId}",
     *   tags={"store"},
     *   summary="Find purchase order by ID",
     *   description="For valid response try integer IDs with value >= 1 and <= 10. Other values will generated exceptions",
     *   operationId="getOrderById",
     *   @OA\Parameter(
     *     name="orderId",
     *     in="path",
     *     description="ID of pet that needs to be fetched",
     *     required=true,
     *     @OA\Schema(
     *         type="integer",
     *         format="int64",
     *         minimum=1.0,
     *         maximum=10.0
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="successful operation",
     *     @OA\Schema(ref="#/components/schemas/Order")
     *   ),
     *   @OA\Response(response=400, description="Invalid ID supplied"),
     *   @OA\Response(response=404, description="Order not found")
     * )
     */
    public function getOrderById()
    {
    }

    /**
     * @OA\Delete(path="/store/order/{orderId}",
     *   tags={"store"},
     *   summary="Delete purchase order by ID",
     *   description="For valid response try integer IDs with positive integer value. Negative or non-integer values will generate API errors",
     *   operationId="deleteOrder",
     *   @OA\Parameter(
     *     name="orderId",
     *     in="path",
     *     required=true,
     *     description="ID of the order that needs to be deleted",
     *     @OA\Schema(
     *         type="integer",
     *         format="int64",
     *         minimum=1.0
     *     )
     *   ),
     *   @OA\Response(response=400, description="Invalid ID supplied"),
     *   @OA\Response(response=404, description="Order not found")
     * )
     */
    public function deleteOrder()
    {
    }
}
