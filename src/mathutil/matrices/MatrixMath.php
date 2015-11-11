<?php
/**
 * Copyright 2015-2016 Tim Jurcka <sdgoij@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
namespace encog\mathutil\matrices;

use encog\mathutil\matrices\decomposition\LUDecomposition;

/**
 * This class can perform many different mathematical operations on matrices.
 * The matrices passed in will not be modified, rather a new matrix, with the
 * operation performed, will be returned.
 */
final class MatrixMath {
	public static function add(Matrix $a, Matrix $b): Matrix {
		if ($a->getRows() != $b->getRows()) {
			throw new MatrixError(
				"To add the matrices they must have the same number of "
				. "rows and columns. Matrix a has {$a->getRows()} "
				. "rows and matrix b has {$b->getRows()} rows.");
		}
		if ($a->getCols() != $b->getCols()) {
			throw new MatrixError(
				"To add the matrices they must have the same number "
				. "of rows and columns. Matrix a has {$a->getCols()} cols "
				. "and matrix b has {$b->getCols()} cols.");
		}
		$dataA = $a->getData();
		$dataB = $b->getData();
		$result = [];
		for ($i = 0; $i < $a->getRows(); $i++) {
			for ($j = 0; $j < $a->getCols(); $j++) {
				$result[$i][$j] = $dataA[$i][$j] + $dataB[$i][$j];
			}
		}
		return new Matrix($result);
	}

	public static function copy(Matrix $source, Matrix &$target) {
		$target = clone $source;
	}

	public static function deleteCol(Matrix $source, int $delete): Matrix {
		if ($delete >= $source->getCols()) {
			throw new MatrixError("Can't delete column $delete from matrix, it only has {$source->getCols()} columns.");
		}
		$src = $source->getData();
		$new = [];

		for ($r = 0; $r < $source->getRows(); $r++) {
			for ($c = 0, $t = 0; $c < $source->getCols(); $c++) {
				if ($c != $delete) {
					$new[$r][$t++] = $src[$r][$c];
				}
			}
		}
		return new Matrix($new);
	}

	public static function deleteRow(Matrix $source, int $delete): Matrix {
		if ($delete >= $source->getCols()) {
			throw new MatrixError("Can't delete row $delete from matrix, it only has {$source->getRows()} rows.");
		}
		$src = $source->getData();
		$new = [];

		for ($r = 0, $t = 0; $r < $source->getRows(); $r++) {
			if ($r != $delete) {
				for ($c = 0; $c < $source->getCols(); $c++) {
					$new[$t][$c] = $src[$r][$c];
				}
				$t++;
			}
		}
		return new Matrix($new);
	}

	public static function determinant(Matrix $matrix): float {
		return (new LUDecomposition($matrix))->det();
	}

	public static function divide(Matrix $a, float $b): Matrix {
		$data = $a->getData();
		$result = [];
		for ($r = 0; $r < $a->getRows(); $r++) {
			for ($c = 0; $c < $a->getCols(); $c++) {
				$result[$r][$c] = $data[$r][$c] / $b;
			}
		}
		return new Matrix($result);
	}

	public static function dotProduct(Matrix $a, Matrix $b): float {
		if (!$a->isVector() || !$b->isVector()) {
			throw new MatrixError("To take the dot product, both matrices must be vectors.");
		}
		$dataA = $a->getData();
		$dataB = $b->getData();

		$lenA = count($dataA) == 1 ? count($dataA[0]) : count($dataA);
		$lenB = count($dataB) == 1 ? count($dataB[0]) : count($dataB);

		if ($lenA != $lenB) {
			throw new MatrixError("To take the dot product, both matrices must be of the same length.");
		}

		$result = 0;
		if (count($dataA) == 1 && count($dataB) == 1) {
			for ($i = 0; $i < $lenA; $i++) {
				$result += $dataA[0][$i] * $dataB[0][$i];
			}
		} else if (count($dataA) == 1 && count($dataB[0]) == 1) {
			for ($i = 0; $i < $lenA; $i++) {
				$result += $dataA[0][$i] * $dataB[$i][0];
			}
		} else if (count($dataA[0]) == 1 && count($dataB) == 1) {
			for ($i = 0; $i < $lenA; $i++) {
				$result += $dataA[$i][0] * $dataB[0][$i];
			}
		} else if (count($dataA[0]) == 1 && count($dataB[0]) == 1) {
			for ($i = 0; $i < $lenA; $i++) {
				$result += $dataA[$i][0] * $dataB[$i][0];
			}
		}
		return $result;
	}

	public static function identity(int $size): Matrix {
		if ($size < 1) {
			throw new MatrixError("Identity matrix must be at least of size 1.");
		}
		$result = Matrix::createZero($size, $size);
		for ($i = 0; $i < $size; $i++) {
			$result->getData()[$i][$i] = 1;
		}
		return $result;
	}

	public static function multiply(Matrix $a, Matrix $b): Matrix {
		if ($a->getCols() != $b->getRows()) {
			throw new MatrixError(
				"To use ordinary matrix multiplication the number of "
				. "columns on the first matrix must match the number of "
				. "rows on the second."
			);
		}
		$x = Matrix::createZero($a->getRows(), $b->getCols());
		$dataA = $a->getData();
		$dataB = $b->getData();
		$bColJ = [];

		for ($j = 0; $j < $b->getCols(); $j++) {
			for ($k = 0; $k < $a->getCols(); $k++) {
				$bColJ[$k] = $dataB[$k][$j];
			}
			for ($i = 0; $i < $a->getRows(); $i++) {
				for ($k = 0, $s = 0; $k < $a->getCols(); $k++) {
					$s += $dataA[$i][$k] * $bColJ[$k];
				}
				$x->getData()[$i][$j] = $s;
			}
		}
		return $x;
	}

	public static function multiplyBy(Matrix $matrix, float $value): Matrix {
		$data = $matrix->getData();
		$result = [];

		for ($r = 0; $r < $matrix->getRows(); $r++) {
			for ($c = 0; $c < $matrix->getCols(); $c++) {
				$result[$r][$c] = $data[$r][$c] * $value;
			}
		}
		return new Matrix($result);
	}

	public static function multiplyVector(Matrix $matrix, array $vector): array {
		$result = [];
		for ($r = 0; $r < $matrix->getRows(); $r++) {
			$result[$r] = 0;
			for ($c = 0; $c < $matrix->getCols(); $c++) {
				$result[$r] += $matrix->get($r, $c) * $vector[$c];
			}
		}
		return $result;
	}

	public static function subtract(Matrix $a, Matrix $b): Matrix {
		if ($a->getRows() != $b->getRows()) {
			throw new MatrixError(
				"To subtract the matrices they must have the same number of "
				. "rows and columns. Matrix a has {$a->getRows()} "
				. "rows and matrix b has {$b->getRows()} rows.");
		}
		if ($a->getCols() != $b->getCols()) {
			throw new MatrixError(
				"To subtract the matrices they must have the same number of "
				. "rows and columns. Matrix a has {$a->getCols()} "
				. "cols and matrix b has {$b->getCols()} cols.");
		}
		$dataA = $a->getData();
		$dataB = $b->getData();
		$result = [];
		for ($i = 0; $i < $a->getRows(); $i++) {
			for ($j = 0; $j < $a->getCols(); $j++) {
				$result[$i][$j] = $dataA[$i][$j] - $dataB[$i][$j];
			}
		}
		return new Matrix($result);
	}

	public static function transpose(Matrix $input): Matrix {
		for ($r = 0, $t = []; $r < $input->getRows(); $r++) {
			for ($c = 0; $c < $input->getCols(); $c++) {
				$t[$c][$r] = $input->get($r, $c);
			}
		}
		return new Matrix($t);
	}

	public static function vectorLength(Matrix $input): float {
		if (!$input->isVector()) {
			throw new MatrixError("Can only take the vector length of a vector.");
		}
		$value = 0.0;
		foreach ($input->toPackedArray() as $v) {
			$value += pow($v, 2);
		}
		return sqrt($value);
	}

	private function __construct() {
	}
}
