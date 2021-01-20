<?php

namespace PetstoreIO;

/**
 * @OA\Schema(@OA\Xml(name="User"))
 */
class User
{

    /**
     * @OA\Property(format="int64")
     * @var int
     */
    public $id;

    /**
     * @OA\Property()
     * @var string
     */
    public $username;

    /**
     * @OA\Property()
     * @var string
     */
    public $firstName;

    /**
     * @OA\Property()
     * @var string
     */
    public $lastName;

    /**
     * @var string
     * @OA\Property()
     */
    public $email;

    /**
     * @var string
     * @OA\Property()
     */
    public $password;

    /**
     * @var string
     * @OA\Property()
     */
    public $phone;

    /**
     * User Status
     * @var int
     * @OA\Property(format="int32")
     */
    public $userStatus;
}
