<?php declare(strict_types=1);

namespace OpenApi\Tests\Fixtures\Processors;

/**
 * Entity controller interface.
 */
interface EntityControllerInterface
{

    /**
     * @OA\POST(
     *   tags={"EntityController"},
     *   path="entity/{id}",
     *   @OA\Response(
     *       response="default",
     *       description="successful operation"
     *   )
     * )
     */
    public function updateEntity($id);
}
