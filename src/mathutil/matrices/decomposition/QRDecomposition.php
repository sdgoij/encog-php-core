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

use encog\mathutil\EncogMath;
use encog\mathutil\matrices\Matrix;
use encog\mathutil\matrices\MatrixError;

/**
 * QR Decomposition.
 *
 * For an m-by-n matrix A with m ≥ n, the QR decomposition is an m-by-n
 * orthogonal matrix Q and an n-by-n upper triangular matrix R so that A = Q*R.
 *
 * The QR decomposition always exists, even if the matrix does not have full
 * rank, so the constructor will never fail. The primary use of the QR
 * decomposition is in the least squares solution of non square systems of
 * simultaneous linear equations. This will fail if isFullRank() returns false.
 *
 * This file based on a class from the public domain JAMA package.
 *   http://math.nist.gov/javanumerics/jama/
 */
class QRDecomposition {
	private $QR;
	private $m, $n;
	private $rd;

	public function __construct(Matrix $other) {
		$this->QR = $other->getArrayCopy();
		$this->m = $other->getRows();
		$this->n = $other->getCols();
		$this->rd = array_fill(0, $this->n, 0.0);

		for ($k = 0; $k < $this->n; $k++) {
			for ($i = $k, $n = 0.0; $i < $this->m; $i++) {
				$n = EncogMath::hypot($n, $this->QR[$i][$k]);
			}
			if ($n != 0.0) {
				if ($this->QR[$k][$k] < 0) {
					$n = -$n;
				}
				for ($i = $k; $i < $this->m; $i++) {
					$this->QR[$i][$k] /= $n;
				}
				$this->QR[$k][$k] += 1.0;
			}
			for ($j = $k+1; $j < $this->n; $j++) {
				for ($i = $k, $s = 0.0; $i < $this->m; $i++) {
					$s += $this->QR[$i][$k] * $this->QR[$i][$j];
				}
				if ($s) {
					$s = -$s / $this->QR[$k][$k];
				}
				for ($i = $k; $i < $this->m; $i++) {
					$this->QR[$i][$j] += $s*$this->QR[$i][$k];
				}
			}
			$this->rd[$k] = -$n;
		}
	}

	public function isFullRange(): bool {
		foreach ($this->rd as $v) {
			if (!$v) return false;
		}
		return true;
	}

	public function getH(): Matrix {
		$m = [[]];
		for ($i = 0; $i < $this->m; $i++) {
			for ($j = 0; $j < $this->n; $j++) {
				if ($i >= $j) {
					$m[$i][$j] = $this->QR[$i][$j];
				} else {
					$m[$i][$j] = 0.0;
				}
			}
		}
		return new Matrix($m);
	}

	public function getR(): Matrix {
		$m = Matrix::createZero($this->n, $this->n);
		for ($i = 0; $i < $this->n; $i++) {
			for ($j = 0; $j < $this->n; $j++) {
				if ($i < $j) {
					$m->getData()[$i][$j] = $this->QR[$i][$j];
				} else if ($i == $j) {
					$m->getData()[$i][$j] = $this->rd[$i];
				} else {
					$m->getData()[$i][$j] = 0.0;
				}
			}
		}
		return $m;
	}

	public function getQ(): Matrix {
		$m = [[]];
		for ($k = $this->n-1; $k >= 0; $k--) {
			for ($i = 0; $i < $this->m; $i++) {
				$m[$i][$k] = 0.0;
			}
			$m[$k][$k] = 1.0;
			for ($j = $k; $j < $this->n; $j++) {
				if ($this->QR[$k][$k] != 0) {
					for ($i = $k, $s = 0.0; $i < $this->m; $i++) {
						$s += $this->QR[$i][$k] * $m[$i][$j];
					}
					if ($s) {
						$s = -$s / $this->QR[$k][$k];
					}
					for ($i = $k; $i < $this->m; $i++) {
						$m[$i][$j] += $s * $this->QR[$i][$k];
					}
				}
			}
		}
		return new Matrix($m);
	}

	public function solve(Matrix $other): Matrix {
		if ($other->getRows() != $this->m) {
			throw new MatrixError("Matrix row dimensions must agree.");
		}
		if (!$this->isFullRange()) {
			throw new MatrixError("Matrix is rank deficient.");
		}
		$nx = $other->getCols();
		$m = $other->getArrayCopy();
		for ($k = 0; $k < $this->n; $k++) {
			for ($j = 0; $j < $nx; $j++) {
				for ($i = $k, $s = 0.0; $i < $this->m; $i++) {
					$s += $this->QR[$i][$k] * $m[$i][$j];
				}
				if ($s) {
					$s = -$s / $this->QR[$k][$k];
				}
				for ($i = $k; $i < $this->m; $i++) {
					$m[$i][$j] += $s * $this->QR[$i][$k];
				}
			}
		}
		for ($k = $this->n-1; $k >= 0; $k--) {
			for ($j = 0; $j < $nx; $j++) {
				$m[$k][$j] /= $this->rd[$k];
			}
			for ($i = 0; $i < $k; $i++) {
				for ($j = 0; $j < $nx; $j++) {
					$m[$i][$j] -= $m[$k][$j] * $this->QR[$i][$k];
				}
			}
		}
		return (new Matrix($m))->getMatrix(range(0, $this->n-1), 0, $nx-1);
	}
}
