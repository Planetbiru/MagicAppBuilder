<?php

namespace MagicObject\Matrix;

use MagicObject\Exceptions\MatrixCalculationException;

/**
 * Class Matrix
 *
 * Provides basic operations for 2D matrices with real number elements,
 * including addition, subtraction, multiplication, and element-wise division.
 * 
 * All matrices must be well-formed (i.e., rectangular and of compatible sizes for the operation).
 * 
 * @author Kamshory
 * @package MagicObject\Database
 * @link https://github.com/Planetbiru/MagicObject
 */
class Matrix
{
    /**
     * Adds two matrices element-wise.
     *
     * @param array $a First matrix.
     * @param array $b Second matrix.
     * @return array Resulting matrix after addition.
     * @throws MatrixCalculationException If matrix dimensions do not match.
     */
    public function add($a, $b)
    {
        $this->validateSameDimensions($a, $b);

        $result = [];
        foreach ($a as $i => $row) {
            foreach ($row as $j => $value) {
                $result[$i][$j] = $value + $b[$i][$j];
            }
        }
        return $result;
    }

    /**
     * Subtracts matrix B from matrix A element-wise.
     *
     * @param array $a First matrix.
     * @param array $b Second matrix.
     * @return array Resulting matrix after subtraction.
     * @throws MatrixCalculationException If matrix dimensions do not match.
     */
    public function subtract($a, $b)
    {
        $this->validateSameDimensions($a, $b);

        $result = [];
        foreach ($a as $i => $row) {
            foreach ($row as $j => $value) {
                $result[$i][$j] = $value - $b[$i][$j];
            }
        }
        return $result;
    }

    /**
     * Multiplies two matrices using standard matrix multiplication.
     *
     * @param array $a First matrix.
     * @param array $b Second matrix.
     * @return array Resulting matrix after multiplication.
     * @throws MatrixCalculationException If matrix dimensions are not compatible for multiplication.
     */
    public function multiply($a, $b)
    {
        $rowsA = count($a);
        $colsA = count($a[0]);
        $rowsB = count($b);
        $colsB = count($b[0]);

        if ($colsA !== $rowsB) {
            throw new MatrixCalculationException("Matrix dimensions are not compatible for multiplication.");
        }

        $result = [];
        for ($i = 0; $i < $rowsA; $i++) {
            for ($j = 0; $j < $colsB; $j++) {
                $sum = 0;
                for ($k = 0; $k < $colsA; $k++) {
                    $sum += $a[$i][$k] * $b[$k][$j];
                }
                $result[$i][$j] = $sum;
            }
        }

        return $result;
    }

    /**
     * Divides matrix A by matrix B element-wise.
     *
     * @param array $a First matrix.
     * @param array $b Second matrix.
     * @return array Resulting matrix after element-wise division.
     * @throws MatrixCalculationException If dimensions do not match or division by zero occurs.
     */
    public function divide($a, $b)
    {
        $this->validateSameDimensions($a, $b);

        $result = [];
        foreach ($a as $i => $row) {
            foreach ($row as $j => $value) {
                if ($b[$i][$j] == 0) {
                    throw new MatrixCalculationException("Division by zero at position [$i][$j].");
                }
                $result[$i][$j] = $value / $b[$i][$j];
            }
        }
        return $result;
    }

    /**
     * Validates that two matrices have the same dimensions.
     *
     * @param array $a First matrix.
     * @param array $b Second matrix.
     * @throws MatrixCalculationException If dimensions do not match.
     */
    private function validateSameDimensions(array $a, array $b): void
    {
        if (count($a) !== count($b)) {
            throw new MatrixCalculationException("Matrices must have the same number of rows.");
        }

        foreach ($a as $i => $row) {
            if (!isset($b[$i]) || count($row) !== count($b[$i])) {
                throw new MatrixCalculationException("Matrices must have the same number of columns in each row (row $i).");
            }
        }
    }
}
