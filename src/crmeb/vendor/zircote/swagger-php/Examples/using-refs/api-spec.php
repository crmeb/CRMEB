<?php

/**
 * @OA\Info(
 *   version="1.0.0",
 *   title="Example of using references in swagger-php",
 * )
 */
?>
You can define top-level parameters which can be references with $ref="#/components/parameters/$parameter"
<?php
/**
 * @OA\Parameter(
 *   parameter="product_id_in_path_required",
 *   name="product_id",
 *   description="The ID of the product",
 *   @OA\Schema(
 *     type="integer",
 *     format="int64",
 *   ),
 *   in="path",
 *   required=true
 * )
 *
 * @OA\RequestBody(
 *   request="product_in_body",
 *   required=true,
 *   description="product_request",
 *   @OA\JsonContent(ref="#/components/schemas/Product")
 * )
 */
?>
You can define top-level responses which can be references with $ref="#/components/responses/$response"

I find it usefull to add @OA\Response(ref="#/components/responses/todo") to the operations when i'm starting out with writting the swagger documentation.
As it bypasses the "@OA\Get() requires at least one @OA\Response()" error and you'll get a nice list of the available api calls in swagger-ui.

Then later, a search for '#/components/responses/todo' will reveal the operations I haven't documented yet.
<?php
/**
 * @OA\Response(
 *   response="product",
 *   description="All information about a product",
 *   @OA\JsonContent(ref="#/components/schemas/Product")
 * )
 *
 * @OA\Response(
 *   response="todo",
 *   description="This API call has no documentated response (yet)",
 * )
 */
?>

And although definitions are generally used for model-level schema's' they can be used for smaller things as well.
Like a @OA\Schema, @OA\Property or @OA\Items that is uses multiple times.

<?php
/**
 * @OA\Schema(
 *   schema="product_status",
 *   type="string",
 *   description="The status of a product",
 *   enum={"available", "discontinued"},
 *   default="available"
 * )
 */
