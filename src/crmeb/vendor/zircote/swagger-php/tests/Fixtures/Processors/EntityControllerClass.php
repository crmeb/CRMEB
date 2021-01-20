<?php declare(strict_types=1);

namespace OpenApi\Tests\Fixtures\Processors;

/**
 * An entity controller class.
 *
 * @OA\Info(version="1.0.0")
 */
class EntityControllerClass implements EntityControllerInterface
{
    use EntityControllerTrait;

    /**
     * @OA\Get(
     *   tags={"EntityController"},
     *   path="entity/{id}",
     *   @OA\Response(
     *       response="default",
     *       description="successful operation"
     *   )
     * )
     */
    public function getEntry($id)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function updateEntity($id)
    {
    }
}
