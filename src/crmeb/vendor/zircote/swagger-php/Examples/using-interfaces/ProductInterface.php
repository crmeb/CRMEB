<?php

namespace UsingInterfaces;

/**
 * @OA\Schema()
 */
interface ProductInterface
{

    /**
     * The product name.
     *
     * @OA\Property(property="name", example="toaster")
     */
    public function getName();
}
