<?php

namespace PetstoreIO;

final class PetController
{

    /**
     * @OA\Get(
     *     path="/pet/findByTags",
     *     summary="Finds Pets by tags",
     *     tags={"pet"},
     *     description="Muliple tags can be provided with comma separated strings. Use tag1, tag2, tag3 for testing.",
     *     operationId="findPetsByTags",
     *     @OA\Parameter(
     *         name="tags",
     *         in="query",
     *         description="Tags to filter by",
     *         required=true,
     *         @OA\Schema(
     *           type="array",
     *           @OA\Items(type="string"),
     *         ),
     *         style="form"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\Schema(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Pet")
     *         ),
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Invalid tag value",
     *     ),
     *     security={
     *         {"petstore_auth": {"write:pets", "read:pets"}}
     *     },
     *     deprecated=true
     * )
     */
    public function findByTags()
    {
    }

    /**
     * @OA\Get(
     *     path="/pet/findByStatus",
     *     summary="Finds Pets by status",
     *     description="Multiple status values can be provided with comma separated strings",
     *     operationId="findPetsByStatus",
     *     tags={"pet"},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Status values that need to be considered for filter",
     *         required=true,
     *         @OA\Schema(
     *         type="array",
     *           @OA\Items(
     *               type="string",
     *               enum={"available", "pending", "sold"},
     *               default="available"
     *           ),
     *         ),
     *         style="form"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\JsonContent(
     *            type="array",
     *            @OA\Items(ref="#/components/schemas/Pet")
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Invalid status value",
     *     ),
     *     security={
     *       {"petstore_auth": {"write:pets", "read:pets"}}
     *     }
     * )
     */
    public function findByStatus()
    {
    }

    /**
     * @OA\Get(
     *     path="/pet/{petId}",
     *     summary="Find pet by ID",
     *     description="Returns a single pet",
     *     operationId="getPetById",
     *     tags={"pet"},
     *     @OA\Parameter(
     *         description="ID of pet to return",
     *         in="path",
     *         name="petId",
     *         required=true,
     *         @OA\Schema(
     *           type="integer",
     *           format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Pet")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Invalid ID supplied"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Pet not found"
     *     ),
     *     security={
     *       {"api_key": {}}
     *     }
     * )
     */
    public function getPetById()
    {
    }

    /**
     * @OA\Post(
     *     path="/pet",
     *     tags={"pet"},
     *     operationId="addPet",
     *     summary="Add a new pet to the store",
     *     description="",
     *     @OA\RequestBody(
     *         description="Pet object that needs to be added to the store",
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Pet"),
     *         @OA\MediaType(
     *             mediaType="application/xml",
     *             @OA\Schema(ref="#/components/schemas/Pet")
     *         ),
     *     ),
     *     @OA\RequestBody(
     *         description="Pet object that needs to be added to the store",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/xml",
     *             @OA\Schema(ref="#/components/schemas/Pet")
     *         )
     *     ),
     *     @OA\Response(
     *         response=405,
     *         description="Invalid input",
     *     ),
     *     security={{"petstore_auth":{"write:pets", "read:pets"}}}
     * )
     */
    public function addPet()
    {
    }

    /**
     * @OA\Put(
     *     path="/pet",
     *     tags={"pet"},
     *     operationId="updatePet",
     *     summary="Update an existing pet",
     *     description="",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Pet object that needs to be added to the store",
     *         @OA\JsonContent(ref="#/components/schemas/Pet"),
     *         @OA\MediaType(
     *            mediaType="application/xml",
     *            @OA\Schema(ref="#/components/schemas/Pet"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID supplied",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Pet not found",
     *     ),
     *     @OA\Response(
     *         response=405,
     *         description="Validation exception",
     *     ),
     *     security={{"petstore_auth":{"write:pets", "read:pets"}}}
     * )
     */
    public function updatePet()
    {
    }

    /**
     * @OA\Delete(
     *     path="/pet/{petId}",
     *     summary="Deletes a pet",
     *     description="",
     *     operationId="deletePet",
     *     tags={"pet"},
     *     @OA\Parameter(
     *         description="Pet id to delete",
     *         in="path",
     *         name="petId",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Header(
     *         header="api_key",
     *         description="Api key header",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID supplied"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Pet not found"
     *     ),
     *     security={{"petstore_auth":{"write:pets", "read:pets"}}}
     * )
     */
    public function deletePet()
    {
    }

    /**
     * @OA\Post(
     *   path="/pet/{petId}",
     *   tags={"pet"},
     *   summary="Updates a pet in the store with form data",
     *   description="",
     *   operationId="updatePetWithForm",
     *   @OA\RequestBody(
     *       required=false,
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="name",
     *                   description="Updated name of the pet",
     *                   type="string"
     *               ),
     *               @OA\Property(
     *                   property="status",
     *                   description="Updated status of the pet",
     *                   type="string"
     *               ),
     *           )
     *       )
     *   ),
     *   @OA\Parameter(
     *     name="petId",
     *     in="path",
     *     description="ID of pet that needs to be updated",
     *     required=true,
     *     @OA\Schema(
     *         type="integer",
     *         format="int64"
     *     )
     *   ),
     *   @OA\Response(response="405",description="Invalid input"),
     *   security={{
     *     "petstore_auth": {"write:pets", "read:pets"}
     *   }}
     * )
     */
    public function updatePetWithForm()
    {
    }

    /**
     * @OA\Post(
     *     path="/pet/{petId}/uploadImage",
     *     description="",
     *     summary="uploads an image",
     *     operationId="uploadFile",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     description="Additional data to pass to server",
     *                     property="additionalMetadata",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     description="file to upload",
     *                     property="file",
     *                     type="string",
     *                     format="file",
     *                 ),
     *                 required={"file"}
     *             )
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="ID of pet to update",
     *         in="path",
     *         name="petId",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         ),
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="successful operation",
     *         @OA\Schema(ref="#/components/schemas/ApiResponse")
     *     ),
     *     security={
     *         {
     *             "petstore_auth": {
     *                  "read:pets",
     *                  "write:pets"
     *             }
     *         }
     *     },
     *     tags={
     *         "pet"
     *     }
     * )
     * */
    public function uploadFile()
    {
    }
}
