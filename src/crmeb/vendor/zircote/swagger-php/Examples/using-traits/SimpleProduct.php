<?php

namespace UsingTraits;

use UsingTraits\Decoration;

/**
 * @OA\Schema(title="SimpleProduct model")
 * )
 */
class SimpleProduct {
    use Decoration\Bells;

    /**
     * The unique identifier of a simple product in our catalog.
     *
     * @var integer
     * @OA\Property(format="int64", example=1)
     */
    public $id;
}
