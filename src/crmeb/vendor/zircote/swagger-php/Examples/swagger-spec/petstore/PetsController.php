<?php

namespace petstore;

class PetsController
{

    /**
     * @OA\Get(
     *     path="/pets",
     *     summary="List all pets",
     *     operationId="listPets",
     *     tags={"pets"},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="How many items to return at one time (max 100)",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             format="int32"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="An paged array of pets",
     *         @OA\Schema(ref="#/components/schemas/Pets"),
     *         @OA\Header(header="x-next", @OA\Schema(type="string"), description="A link to the next page of responses")
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="unexpected error",
     *         @OA\Schema(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function listPets()
    {
    }

    /**
     * @OA\Post(
     *    path="/pets",
     *    summary="Create a pet",
     *    operationId="createPets",
     *    tags={"pets"},
     *    @OA\Response(response=201, description="Null response"),
     *    @OA\Response(
     *        response="default",
     *        description="unexpected error",
     *        @OA\Schema(ref="#/components/schemas/Error")
     *    )
     * )
     */
    public function createPets()
    {
    }

    /**
     * @OA\Get(
     *     path="/pets/{petId}",
     *     summary="Info for a specific pet",
     *     operationId="showPetById",
     *     tags={"pets"},
     *     @OA\Parameter(
     *         name="petId",
     *         in="path",
     *         required=true,
     *         description="The id of the pet to retrieve",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Expected response to a valid request",
     *         @OA\Schema(ref="#/components/schemas/Pets")
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="unexpected error",
     *         @OA\Schema(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function showPetById($id)
    {
    }
}
