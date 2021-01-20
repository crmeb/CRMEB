Merging of traits got a bit of an overhaul in 3.0.4/5.

By default traits annotations are now inherited via `allOf`. This is done by the `InheritTraits` processor.

If this is not working for you this processor can be swapped for the `MergeTraits` (this used to be the default).

<?php

/**
 * @OA\Info(
 *   version="1.0.0",
 *   title="Example of using traits in swagger-php",
 * )
 */
