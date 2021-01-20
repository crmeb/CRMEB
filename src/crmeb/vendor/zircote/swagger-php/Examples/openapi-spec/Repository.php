<?php
namespace OpenApi\LinkExample;

/**
 * @OA\Schema(schema="repository")
 */
class Repository
{

    /**
     * @OA\Property()
     * @var string
     */
    public $slug;

    /**
     * @OA\Property()
     * @var User
     */
    public $owner;
}
