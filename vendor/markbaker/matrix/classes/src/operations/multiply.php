<?php

/**
 *
 * Function code for the matrix multiplication operation
 *
 * @copyright  Copyright (c) 2018 Mark Baker (https://github.com/MarkBaker/PHPMatrix)
 * @license    https://opensource.org/licenses/MIT    MIT
 */
namespace Matrix;

use Matrix\Operators\Multiplication;

/**
 * Multiplies two or more matrices
 *
 * @param     mixed[]    $matrixValues   The matrices to multiply
 * @return    Matrix
 * @throws    Exception
 */
function multiply(...$matrixValues)
{
    if (count($matrixValues) < 2) {
        throw new Exception('This operation requires at least 2 arguments');
    }

    $matrix = array_shift($matrixValues);
    if (!is_object($matrix) || !($matrix instanceof Matrix)) {
        $matrix = new Matrix($matrix);
    }

    $result = new Multiplication($matrix);

    foreach ($matrixValues as $matrix) {
        $result->execute($matrix);
    }

    return $result->result();
}
