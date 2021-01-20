<?php

namespace UsingTraits;

/**
 * @OA\Schema(title="Colour trait")
 */
trait Colour {

    /**
     * The colour.
     *
     * @OA\Property(example="red")
     */
    public $colour;
}
