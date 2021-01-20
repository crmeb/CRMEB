<?php declare(strict_types=1);

namespace OpenApi\Tests\Fixtures\Parser;

use OpenApi\Annotations as OA;

interface UserInterface extends OtherInterface
{

    /**
     * The first name of the user.
     *
     * @return string
     * @example John
     * @OA\Property()
     */
    public function getFirstName();
}
