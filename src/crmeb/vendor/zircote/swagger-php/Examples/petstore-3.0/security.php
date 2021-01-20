<?php

/**
 * @license Apache 2.0
 */

/**
 * @OA\SecurityScheme(
 *     type="oauth2",
 *     name="petstore_auth",
 *     securityScheme="petstore_auth",
 *     @OA\Flow(
 *         flow="implicit",
 *         authorizationUrl="http://petstore.swagger.io/oauth/dialog",
 *         scopes={
 *             "write:pets": "modify pets in your account",
 *             "read:pets": "read your pets",
 *         }
 *     )
 * )
 * @OA\SecurityScheme(
 *     type="apiKey",
 *     in="header",
 *     securityScheme="api_key",
 *     name="api_key"
 * )
 */
