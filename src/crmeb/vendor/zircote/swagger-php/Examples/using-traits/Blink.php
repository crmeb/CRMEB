<?php

namespace UsingTraits;

/**
 * @OA\Schema(title="Blink trait")
 */
trait Blink {

    /**
     * The frequency.
     *
     * @OA\Property(example=1)
     */
    public $frequency;
}
