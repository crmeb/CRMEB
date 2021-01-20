<?php

/**
 * @license Apache 2.0
 */

namespace Petstore30;

/**
 * Class User
 *
 * @package Petstore30
 *
 * @author  Donii Sergii <doniysa@gmail.com>
 *
 * @OA\Schema(
 *     title="User model",
 *     description="User model",
 * )
 */
class User
{
    /**
     * @OA\Property(
     *     format="int64",
     *     description="ID",
     *     title="ID",
     * )
     *
     * @var integer
     */
    private $id;

    /**
     * @OA\Property(
     *     description="Username",
     *     title="Username",
     * )
     *
     * @var string
     */
    private $username;

    /**
     * @OA\Property(
     *     description="First name",
     *     title="First name",
     * )
     *
     * @var string
     */
    private $firstName;

    /**
     * @OA\Property(
     *     description="Last name",
     *     title="Last name",
     * )
     *
     * @var string
     */
    private $lastName;

    /**
     * @OA\Property(
     *     format="email",
     *     description="Email",
     *     title="Email",
     * )
     *
     * @var string
     */
    private $email;

    /**
     * @OA\Property(
     *     format="int64",
     *     description="Password",
     *     title="Password",
     *     maximum=255
     * )
     *
     * @var string
     */
    private $password;

    /**
     * @OA\Property(
     *     format="msisdn",
     *     description="Phone",
     *     title="Phone",
     * )
     *
     * @var string
     */
    private $phone;

    /**
     * @OA\Property(
     *     format="int32",
     *     description="User status",
     *     title="User status",
     * )
     *
     * @var integer
     */
    private $userStatus;
}
