<?php
namespace OpenApi\LinkExample;

/**
 * @OA\Schema(schema="user")
 */
class User
{

    /**
     * @OA\Property()
     * @var string
     */
    public $username;

    /**
     * @OA\Property()
     * @var string
     */
    public $uuid;
}
