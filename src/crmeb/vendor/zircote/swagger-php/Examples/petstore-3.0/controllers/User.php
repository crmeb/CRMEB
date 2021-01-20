<?php

/**
 * @license Apache 2.0
 */

namespace Petstore30\controllers;

/**
 * Class User
 *
 * @package Petstore30\controllers
 *
 * @author  Donii Sergii <doniysa@gmail.com>
 */
class User
{
    /**
     * @OA\Post(
     *     path="/user",
     *     tags={"user"},
     *     summary="Create user",
     *     description="This can only be done by the logged in user.",
     *     operationId="createUser",
     *     @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     ),
     *     @OA\RequestBody(
     *         description="Create user object",
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     )
     * )
     */
    public function createUser()
    {
    }

    /**
     * @OA\Post(
     *     path="/user/createWithArray",
     *     tags={"user"},
     *     summary="Create list of users with given input array",
     *     operationId="createUsersWithListInput",
     *     @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     ),
     *     @OA\RequestBody(ref="#/components/requestBodies/UserArray")
     * )
     */
    public function createUsersWithListInput()
    {
    }

    /**
     * @OA\Get(
     *     path="/user/login",
     *     tags={"user"},
     *     summary="Logs user into system",
     *     operationId="loginUser",
     *     @OA\Parameter(
     *         name="username",
     *         in="query",
     *         description="The user name for login",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="password",
     *         in="query",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\Header(
     *             header="X-Rate-Limit",
     *             description="calls per hour allowed by the user",
     *             @OA\Schema(
     *                 type="integer",
     *                 format="int32"
     *             )
     *         ),
     *         @OA\Header(
     *             header="X-Expires-After",
     *             description="date in UTC when token expires",
     *             @OA\Schema(
     *                 type="string",
     *                 format="datetime"
     *             )
     *         ),
     *         @OA\JsonContent(
     *             type="string"
     *         ),
     *         @OA\MediaType(
     *             mediaType="application/xml",
     *             @OA\Schema(
     *                 type="string"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid username/password supplied"
     *     )
     * )
     */
    public function loginUser()
    {
    }

    /**
     * @OA\Get(
     *     path="/user/logout",
     *     tags={"user"},
     *     summary="Logs out current logged in user session",
     *     operationId="logoutUser",
     *     @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     )
     * )
     */
    public function logoutUser()
    {
    }

    /**
     * @OA\Get(
     *     path="/user/{username}",
     *     summary="Get user by user name",
     *     operationId="getUserByName",
     *     @OA\Parameter(
     *         name="username",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/User"),
     *         @OA\MediaType(
     *             mediaType="application/xml",
     *             @OA\Schema(ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid username supplied"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     ),
     * )
     */
    public function getUserByName()
    {
    }

    /**
     * @OA\Put(
     *     path="/user/{username}",
     *     summary="Updated user",
     *     description="This can pnly be done by the logged in user.",
     *     operationId="updateUser",
     *     @OA\Parameter(
     *         name="username",
     *         in="path",
     *         description="name that to be updated",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid user supplied"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     ),
     *     @OA\RequestBody(
     *         description="Updated user object",
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     )
     * )
     */
    public function updateUser()
    {
    }

    /**
     * @OA\Delete(
     *     path="/user/{username}",
     *     summary="Delete user",
     *     description="This can only be done by the logged in user.",
     *     operationId="deleteUser",
     *     @OA\Parameter(
     *         name="username",
     *         in="path",
     *         description="The name that needs to be deleted",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid username supplied",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *     )
     * )
     */
    public function deleteUser()
    {
    }
}
