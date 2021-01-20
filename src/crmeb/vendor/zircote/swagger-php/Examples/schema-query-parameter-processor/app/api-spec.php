<?php

namespace App;

/**
 * @OA\Info(
 *   version="1.0.0",
 *   title="Example of using a custom processor in swagger-php",
 * )
 */
?>
Uses a custom processor `QueryArgsFromSchema` processor to convert a vendor extension into query parameters.
The parameters are extracted from the schema referenced by the custom extension.
