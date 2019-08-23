<?php

namespace Matrix\Operators;

use Matrix\Matrix;
use Matrix\Exception;

class DirectSum extends Operator
{
    /**
     * Execute the addition
     *
     * @param mixed $value The matrix or numeric value to add to the current base value
     * @throws Exception If the provided argument is not appropriate for the operation
     * @return $this The operation object, allowing multiple additions to be chained
     **/
    public function execute($value)
    {
        if (is_array($value)) {
            $value = new Matrix($value);
        }

        if (is_object($value) && ($value instanceof Matrix)) {
            return $this->directSumMatrix($value);
        }

        throw new Exception('Invalid argument for addition');
    }

    /**
     * Execute the direct sum for a matrix
     *
     * @param Matrix $value The numeric value to concatenate/direct sum with the current base value
     * @return $this The operation object, allowing multiple additions to be chained
     * @throws Exception If the provided argument is not appropriate for the operation
     **/
    protected function directSumMatrix(Matrix $value)
    {
        $originalColumnCount = count($this->matrix[0]);
        $originalRowCount = count($this->matrix);
        $additionalColumnCount = $value->columns;
        $additionalRowCount = $value->rows;
        $value = $value->toArray();

        for ($row = 0; $row < $this->rows; ++$row) {
            $this->matrix[$row] = array_merge($this->matrix[$row], array_fill(0, $additionalColumnCount, 0));
        }

        $this->matrix = array_merge(
            $this->matrix,
            array_fill(0, $additionalRowCount, array_fill(0, $originalColumnCount, 0))
        );

        for ($row = $originalRowCount; $row < $originalRowCount + $additionalRowCount; ++$row) {
            array_splice(
                $this->matrix[$row],
                $originalColumnCount,
                $additionalColumnCount,
                $value[$row - $originalRowCount]
            );
        }

        return $this;
    }
}
