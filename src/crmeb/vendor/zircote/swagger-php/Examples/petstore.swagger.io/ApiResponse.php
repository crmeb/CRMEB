<?php

namespace PetstoreIO;

/**
 * @OA\Schema()
 */
class ApiResponse
{

    /**
     * @OA\Property(format="int32")
     * @var int
     */
    public $code;

    /**
     * @OA\Property
     * @var string
     */
    public $type;

    /**
     * @OA\Property
     * @var string
     */
    public $message;
}
