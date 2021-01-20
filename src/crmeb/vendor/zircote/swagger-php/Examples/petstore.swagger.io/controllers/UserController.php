<?php

namespace PetstoreIO;

class UserController
{

    /**
     * @OA\Post(path="/user",
     *   tags={"user"},
     *   summary="Create user",
     *   description="This can only be done by the logged in user.",
     *   operationId="createUser",
     *   @OA\RequestBody(
     *       required=true,
     *       description="Created user object",
     *       @OA\MediaType(
     *           mediaType="multipart/form-data",
     *           @OA\Schema(ref="#/components/schemas/User")
     *       )
     *   ),
     *   @OA\Response(response="default", description="successful operation")
     * )
     */
    public function createUser()
    {
    }

    /**
     * @OA\Post(path="/user/createWithArray",
     *   tags={"user"},
     *   summary="Creates list of users with given input array",
     *   description="",
     *   operationId="createUsersWithArrayInput",
     *   @OA\RequestBody(
     *       description="List of user object",
     *       required=true,
     *       @OA\MediaType(
     *           mediaType="multipart/form-data",
     *           @OA\Schema(
     *               type="array",
     *               @OA\Items(ref="#/components/schemas/User")
     *           )
     *       )
     *   ),
     *   @OA\Response(response="default", description="successful operation")
     * )
     */
    public function createUsersWithArrayInput()
    {
    }

    /**
     * @OA\Post(path="/user/createWithList",
     *   tags={"user"},
     *   summary="Creates list of users with given input array",
     *   description="",
     *   operationId="createUsersWithListInput",
     *   @OA\RequestBody(
     *       required=true,
     *       description="List of user object",
     *       @OA\MediaType(
     *           mediaType="multipart/form-data",
     *           @OA\Schema(
     *               type="array",
     *               @OA\Items(ref="#/components/schemas/User")
     *           )
     *       )
     *   ),
     *   @OA\Response(response="default", description="successful operation")
     * )
     */
    public function createUsersWithListInput()
    {
    }

    /**
     * @OA\Get(path="/user/login",
     *   tags={"user"},
     *   summary="Logs user into the system",
     *   description="",
     *   operationId="loginUser",
     *   @OA\Parameter(
     *     name="username",
     *     required=true,
     *     in="query",
     *     description="The user name for login",
     *     @OA\Schema(
     *         type="string"
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="password",
     *     in="query",
     *     @OA\Schema(
     *         type="string",
     *     ),
     *     description="The password for login in clear text",
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="successful operation",
     *     @OA\Schema(type="string"),
     *     @OA\Header(
     *       header="X-Rate-Limit",
     *       @OA\Schema(
     *           type="integer",
     *           format="int32"
     *       ),
     *       description="calls per hour allowed by the user"
     *     ),
     *     @OA\Header(
     *       header="X-Expires-After",
     *       @OA\Schema(
     *          type="string",
     *          format="date-time",
     *       ),
     *       description="date in UTC when token expires"
     *     )
     *   ),
     *   @OA\Response(response=400, description="Invalid username/password supplied")
     * )
     */
    public function loginUser()
    {
    }

    /**
     * @OA\Get(path="/user/logout",
     *   tags={"user"},
     *   summary="Logs out current logged in user session",
     *   description="",
     *   operationId="logoutUser",
     *   parameters={},
     *   @OA\Response(response="default", description="successful operation")
     * )
     */
    public function logoutUser()
    {
    }

    /**
     * @OA\Get(path="/user/{username}",
     *   tags={"user"},
     *   summary="Get user by user name",
     *   description="",
     *   operationId="getUserByName",
     *   @OA\Parameter(
     *     name="username",
     *     in="path",
     *     description="The name that needs to be fetched. Use user1 for testing. ",
     *     required=true,
     *     @OA\Schema(
     *         type="string"
     *     )
     *   ),
     *   @OA\Response(response=200, description="successful operation", @OA\Schema(ref="#/components/schemas/User")),
     *   @OA\Response(response=400, description="Invalid username supplied"),
     *   @OA\Response(response=404, description="User not found")
     * )
     */
    public function getUserByName($username)
    {
    }

    /**
     * @OA\Put(path="/user/{username}",
     *   tags={"user"},
     *   summary="Updated user",
     *   description="This can only be done by the logged in user.",
     *   operationId="updateUser",
     *   @OA\Parameter(
     *     name="username",
     *     in="path",
     *     description="name that need to be updated",
     *     required=true,
     *     @OA\Schema(
     *         type="string"
     *     )
     *   ),
     *   @OA\Response(response=400, description="Invalid user supplied"),
     *   @OA\Response(response=404, description="User not found"),
     *   @OA\RequestBody(
     *       required=true,
     *       description="Updated user object",
     *       @OA\MediaType(
     *           mediaType="multipart/form-data",
     *           @OA\Schema(ref="#/components/schemas/User")
     *       )
     *   ),
     * )
     */
    public function updateUser()
    {
    }

    /**
     * @OA\Delete(path="/user/{username}",
     *   tags={"user"},
     *   summary="Delete user",
     *   description="This can only be done by the logged in user.",
     *   operationId="deleteUser",
     *   @OA\Parameter(
     *     name="username",
     *     in="path",
     *     description="The name that needs to be deleted",
     *     required=true,
     *     @OA\Schema(
     *         type="string"
     *     )
     *   ),
     *   @OA\Response(response=400, description="Invalid username supplied"),
     *   @OA\Response(response=404, description="User not found")
     * )
     */
    public function deleteUser()
    {
    }
}
