<?php
namespace OpenApi\LinkExample;

/**
 * @OA\Schema(schema="pullrequest")
 */
class Repository
{

    /**
     * @OA\Property()
     * @var integer
     */
    public $id;

    /**
     * @OA\Property()
     * @var string
     */
    public $title;

    /**
    * @OA\Property()
    * @var Repository
    */
    public $repository;

    /**
     * @OA\Property()
     * @var User
     */
    public $author;
}
