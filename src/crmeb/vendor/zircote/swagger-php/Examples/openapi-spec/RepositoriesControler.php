<?php

namespace OpenApi\LinkExample;

class RepositoriesController
{
    /**
     * @OA\Get(path="/2.0/repositories/{username}",
     *   operationId="getRepositoriesByOwner",
     *   @OA\Parameter(
     *     name="username",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(response=200,
     *     description="repositories owned by the supplied user",
     *     @OA\JsonContent(type="array",
     *       @OA\Items(ref="#/components/schemas/repository")
     *     ),
     *     @OA\Link(link="userRepository", ref="#/components/links/UserRepository")
     *   )
     * )
     * @OA\Link(link="UserRepositories",
     *   operationId="getRepositoriesByOwner",
     *   parameters={"username"="$response.body#/username"}
     * )
     */
    public function getRepositoriesByOwner($username)
    {
    }

    /**
     ** @OA\Get(path="/2.0/repositories/{username}/{slug}",
     *   operationId="getRepository",
     *   @OA\Parameter(name="username",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(name="slug",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(response=200,
     *       description="The repository",
     *       @OA\JsonContent(ref="#/components/schemas/repository"),
     *       @OA\Link(link="repositoryPullRequests", ref="#/components/links/RepositoryPullRequests")
     *     )
     *   )
     * )
     * @OA\Link(link="UserRepository",
     *   operationId="getRepository",
     *   parameters={
     *     "username"="$response.body#/owner/username",
     *     "slug"="$response.body#/slug"
     *   }
     * )
     */
    public function getRepository()
    {
    }

    /**
     * @OA\Get(path="/2.0/repositories/{username}/{slug}/pullrequests",
     *   operationId="getPullRequestsByRepository",
     *   @OA\Parameter(name="username",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(name="slug",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(name="state",
     *     in="query",
     *     @OA\Schema(type="string",
     *       enum={"open", "merged", "declined"}
     *     )
     *   ),
     *   @OA\Response(response=200,
     *     description="an array of pull request objects",
     *     @OA\JsonContent(type="array",
     *         @OA\Items(ref="#/components/schemas/pullrequest")
     *     )
     *   )
     * )
     * @OA\Link(link="RepositoryPullRequests",
     *   operationId="getPullRequestsByRepository",
     *   parameters={
     *     "username"="$response.body#/owner/username",
     *     "slug"="$response.body#/slug"
     *   }
     * )
     */
    public function getPullRequestsByRepository()
    {
    }

    /**
     * @OA\Get(path="/2.0/repositories/{username}/{slug}/pullrequests/{pid}",
     *   operationId="getPullRequestsById",
     *   @OA\Parameter(name="username",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(name="slug",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(name="pid",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(response=200,
     *     description="a pull request object",
     *     @OA\JsonContent(ref="#/components/schemas/pullrequest"),
     *     @OA\Link(link="pullRequestMerge", ref="#/components/links/PullRequestMerge")
     *   )
     * )
     */
    public function getPullRequestsById()
    {
    }

    /**
     * @OA\Post(path="/2.0/repositories/{username}/{slug}/pullrequests/{pid}/merge",
     *   operationId="mergePullRequest",
     *   @OA\Parameter(name="username",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(name="slug",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(name="pid",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(response=204,
     *     description="the PR was successfully merged"
     *   )
     * )
     * @OA\Link(link="PullRequestMerge",
     *   operationId="mergePullRequest",
     *   parameters={
     *     "username"="$response.body#/author/username",
     *     "slug"="$response.body#/repository/slug",
     *     "pid"="$response.body#/id"
     *   }
     * )
     */
    public function mergePullRequest()
    {
    }
}

?>




