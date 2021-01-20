<?php

/**
 * @license Apache 2.0
 */

/**
 *
 * @OA\RequestBody(
 *     request="Pet",
 *     description="Pet object that needs to be added to the store",
 *     required=true,
 *     @OA\JsonContent(ref="#/components/schemas/Pet"),
 *     @OA\MediaType(
 *         mediaType="application/xml",
 *         @OA\Schema(ref="#/components/schemas/Pet")
 *     )
 * )
 */

/**
 * @OA\RequestBody(
 *     request="UserArray",
 *     description="List of user object",
 *     required=true,
 *     @OA\JsonContent(
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/User")
 *     )
 * )
 */
