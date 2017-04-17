<?php
/**
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
namespace encog\mathutil\matrices\decomposition;

use InvalidArgumentException;

use encog\mathutil\matrices\Matrix;
use encog\mathutil\matrices\MatrixError;

/**
 * LU Decomposition.
 *
 * For an m-by-n matrix A with m ≥ n, the LU decomposition is an m-by-n unit
 * lower triangular matrix L, an n-by-n upper triangular matrix U, and a
 * permutation vector piv of length m so that A(piv,:) = L*U. If m < n, then L
 * is m-by-m and U is m-by-n.
 *
 * The LU decomposition with pivoting always exists, even if the matrix is
 * singular, so the constructor will never fail. The primary use of the LU
 * decomposition is in the solution of square systems of simultaneous linear
 * equations. This will fail if isNonSingular() returns false.
 *
 * This file based on a class from the public domain JAMA package
 *   http://math.nist.gov/javanumerics/jama/
 */
class LUDecomposition {
	private $LU;
	private $m, $n, $pivsign;
	private $piv;

	public function __construct(Matrix $m) {
		$this->LU = $m->getArrayCopy();
		$this->m = $m->getRows();
		$this->n = $m->getCols();
		$this->piv = range(0, $this->m-1);
		$this->pivsign = 1;
		$LUcolj = [];
		for ($j = 0; $j < $this->n; $j++) {
			for ($i = 0; $i < $this->m; $i++) {
				$LUcolj[$i] = $this->LU[$i][$j];
			}
			for ($i = 0; $i < $this->m; $i++) {
				$kmax = min($i, $j);
				$s = 0.0;
				for ($k = 0; $k < $kmax; $k++) {
					$s += $this->LU[$i][$k] * $LUcolj[$k];
				}
				$this->LU[$i][$j] = $LUcolj[$i] -= $s;
			}
			$p = $j;
			for ($i = $j+1; $i < $this->m; $i++) {
				if (abs($LUcolj[$i]) > abs($LUcolj[$p])) {
					$p = $i;
				}
			}
			if ($p != $j) {
				for ($k = 0; $k < $this->n; $k++) {
					$temp = $this->LU[$p][$k];
					$this->LU[$p][$k] = $this->LU[$j][$k];
					$this->LU[$j][$k] = $temp;
				}
				$temp = $this->piv[$p];
				$this->piv[$p] = $this->piv[$j];
				$this->piv[$j] = $temp;
				$this->pivsign = -$this->pivsign;
			}
			if ($j < $this->m & ($this->LU[$j][$j] ?? 0) != 0.0) {
				for ($i = $j+1; $i < $this->m; $i++) {
					$this->LU[$i][$j] /= $this->LU[$j][$j];
				}
			}
		}
	}

	public function isNonSingular(): bool {
		for ($i = 0; $i < $this->n; $i++) {
			if ($this->LU[$i][$i] == 0) {
				return false;
			}
		}
		return true;
	}

	public function getL(): Matrix {
		$m = Matrix::createZero($this->m, $this->n);
		for ($i = 0; $i < $this->m; $i++) {
			for ($j = 0; $j < $this->n; $j++) {
				if ($i > $j) {
					$m->getData()[$i][$j] = $this->LU[$i][$j];
				} else if ($i == $j) {
					$m->getData()[$i][$j] = 1.0;
				} else {
					$m->getData()[$i][$j] = 0.0;
				}
			}
		}
		return $m;
	}

	public function getU(): Matrix {
		$m = Matrix::createZero($this->n, $this->n);
		for ($i = 0; $i < $this->n; $i++) {
			for ($j = 0; $j < $this->n; $j++) {
				if ($i <= $j) {
					$m->getData()[$i][$j] = $this->LU[$i][$j];
				} else {
					$m->getData()[$i][$j] = 0.0;
				}
			}
		}
		return $m;
	}

	public function getPivot(): array {
		return array_slice($this->piv, 0, $this->m);
	}

	public function getDoublePivot(): array {
		for ($i = 0, $p = []; $i < $this->m; $i++) {
			$p[] = (float)$this->piv[$i];
		}
		return $p;
	}

	public function det(): float {
		if ($this->m != $this->n) {
			throw new InvalidArgumentException("Matrix must be square.");
		}
		$d = (float)$this->pivsign;
		for ($i = 0; $i < $this->n; $i++) {
			$d *= $this->LU[$i][$i];
		}
		return $d;
	}

	public function solve(Matrix $other): Matrix {
		if ($other->getRows() != $this->m) {
			throw new InvalidArgumentException("Matrix row dimensions must agree.");
		}
		if (!$this->isNonSingular()) {
			throw new InvalidArgumentException("Matrix is singular.");
		}
		$nx = $other->getCols();
		$m = $other->getMatrix($this->piv, 0, $nx-1);
		for ($k = 0; $k < $this->n; $k++) {
			for ($i = $k+1; $i < $this->n; $i++) {
				for ($j = 0; $j < $nx; $j++) {
					$m->getData()[$i][$j] -= $m->getData()[$k][$j] * $this->LU[$i][$k];
				}
			}
		}
		for ($k = $this->n-1; $k >= 0; $k--) {
			for ($j = 0; $j < $nx; $j++) {
				$m->getData()[$k][$j] /= $this->LU[$k][$k];
			}
			for ($i = 0; $i < $k; $i++) {
				for ($j = 0; $j < $nx; $j++) {
					$m->getData()[$i][$j] -= $m->getData()[$k][$j] * $this->LU[$i][$k];
				}
			}
		}
		return $m;
	}

	public function solveArray(array $values): array {
		if (!count($values)) {
			throw new MatrixError("Empty values.");
		}
		if (count($values) != count($this->LU)) {
			throw new MatrixError("Invalid matrix dimensions.");
		}
		if (!$this->isNonSingular()) {
			throw new MatrixError("Matrix is singular");
		}
		for ($b = [], $i = 0; $i < count($values); $i++) {
			$b[] = $values[$this->piv[$i]];
		}
		$rows = $cols = count($this->LU[0]);
		for ($i = 0, $r = []; $i < $rows; $i++) {
			for ($j = 0, $r[$i] = $b[$i]; $j < $i; $j++) {
				$r[$i] -= $this->LU[$i][$j] * $r[$j];
			}
		}
		for ($i = $rows-1; $i >= 0; $i--) {
			for ($j = $cols-1; $j > $i; $j--) {
				$r[$i] -= $this->LU[$i][$j] * $r[$j];
			}
			$r[$i] /= $this->LU[$i][$i];
		}
		return $r;
	}

	public function inverse(): array {
		if (!$this->isNonSingular()) {
			throw new MatrixError("Matrix is singular");
		}
		$rows = count($this->LU);
		$cols = count($this->LU[0]);
		$r = Matrix::createZero($rows, $cols)->getData();
		for ($i = 0; $i < $rows; $i++) {
			$r[$i][$this->piv[$i]] = 1.0;
		}
		for ($k = 0; $k < $cols; $k++) {
			for ($i = $k+1; $i < $cols; $i++) {
				for ($j = 0; $j < $rows; $j++) {
					$r[$i][$j] -= $r[$k][$j] * $this->LU[$i][$k];
				}
			}
		}
		for ($k = $cols-1; $k >= 0; $k--) {
			for ($j = 0; $j < $rows; $j++) {
				$r[$k][$j] /= $this->LU[$k][$k];
			}
			for ($i = 0; $i < $k; $i++) {
				for ($j = 0; $j < $rows; $j++) {
					$r[$i][$j] -= $r[$k][$j] * $this->LU[$i][$k];
				}
			}
		}
		return $r;
	}
}
