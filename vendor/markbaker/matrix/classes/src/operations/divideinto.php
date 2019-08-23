<?php

/**
 *
 * Function code for the matrix division operation
 *
 * @copyright  Copyright (c) 2013-2018 Mark Baker (https://github.com/MarkBaker/PHPMatrix)
 * @license    https://opensource.org/licenses/MIT    MIT
 */
namespace Matrix;

use Matrix\Operators\Division;

/**
 * Divides two or more matrix numbers
 *
 * @param     array of string|integer|float|Matrix    $matrixValues   The numbers to divide
 * @return    Matrix
 */
function divideinto(...$matrixValues)
{
    if (count($matrixValues) < 2) {
        throw new \Exception('This function requires at least 2 arguments');
    }
    $matrixValues = array_reverse($matrixValues);

    $matrix = array_shift($matrixValues);
    if (!is_object($matrix) || !($matrix instanceof Matrix)) {
        $matrix = new Matrix($matrix);
    }

    $result = new Division($matrix);

    foreach ($matrixValues as $matrix) {
        $result->execute($matrix);
    }

    return $result->result();
}
