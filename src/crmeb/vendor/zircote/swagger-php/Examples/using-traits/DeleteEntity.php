<?php

namespace UsingTraits;

/**
 * @OA\Schema(title="Delete entity trait")
 *
 * @todo Not sure if this is correct or wanted behaviour...
 */
trait DeleteEntity {

    /**
     * @OA\Delete(
     *   tags={"Entities"},
     *   path="/entities/{id}",
     *   @OA\Parameter(
     *     description="ID of entity to delete",
     *     in="path",
     *     name="id",
     *     required=true,
     *     @OA\Schema(
     *       type="string"
     *     )
     *   ),
     *   @OA\Response(
     *       response="default",
     *       description="successful operation"
     *   )
     * )
     */
    public function deleteEntity($id)
    {
    }
}
