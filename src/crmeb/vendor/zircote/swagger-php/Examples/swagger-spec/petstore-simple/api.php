<?php

/**
 * @OA\OpenApi(
 *     @OA\Server(
 *         url="petstore.swagger.io",
 *         description="API server"
 *     ),
 *     @OA\Info(
 *         version="1.0.0",
 *         title="Swagger Petstore",
 *         description="A sample API that uses a petstore as an example to demonstrate features in the swagger-2.0 specification",
 *         termsOfService="http://swagger.io/terms/",
 *         @OA\Contact(name="Swagger API Team"),
 *         @OA\License(name="MIT")
 *     ),
 * )
 */

/**
 * @OA\Schema(
 *     schema="ErrorModel",
 *     required={"code", "message"},
 *     @OA\Property(
 *         property="code",
 *         type="integer",
 *         format="int32"
 *     ),
 *     @OA\Property(
 *         property="message",
 *         type="string"
 *     )
 * )
 */
