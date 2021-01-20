<?php

namespace UsingTraits;

use UsingTraits\Decoration\Bells;

/**
 * @OA\Schema(title="Bells and Whistles trait")
 */
trait BellsAndWhistles {
    use Bells;
    use \UsingTraits\Decoration\Whistles;

    /**
     * The plating.
     *
     * @OA\Property(example="gold")
     */
    public $plating;
}
