<?php

namespace OpenApi\LinkExample;

/**
 * MVC controller that handles "users/*" urls.
 */
class UsersController
{

    /**
     * @OA\Get(path="/2.0/users/{username}",
     *   operationId="getUserByName",
     *   @OA\Parameter(name="username",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(response="200",
     *     description="The User",
     *     @OA\JsonContent(ref="#/components/schemas/user"),
     *     @OA\Link(link="userRepositories", ref="#/components/links/UserRepositories")
     *   )
     * )
     */
    public function getUserByName($username)
    {
    }
}
