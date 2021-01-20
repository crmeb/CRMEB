<?php declare(strict_types=1);

namespace OpenApi\Tests\Fixtures;

class GrandAncestor
{

    /**
     * @OA\Property();
     * @var string
     */
    public $firstname;

    /**
     * @OA\Property(property="lastname");
     * @var string
     */
    public $lastname;
}
