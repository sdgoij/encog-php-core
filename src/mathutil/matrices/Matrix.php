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
namespace encog\mathutil\matrices;

use InvalidArgumentException;
use encog\Encog;
use encog\mathutil\matrices\decomposition\LUDecomposition;
use encog\mathutil\matrices\decomposition\QRDecomposition;
use encog\mathutil\randomize\RangeRandomizer;
use encog\util\Random;
use RangeException;

/**
 * This class implements a mathematical matrix. Matrix math is very important to
 * neural network processing. Many of the neural network classes make use of the
 * matrix classes in this package.
 */
class Matrix {
	public static function createColumnMatrix(array $data): Matrix {
		$m = [];
		foreach ($data as $row => $value) {
			$m[$row][] = $value;
		}
		return new Matrix($m);
	}

	public static function createRowMatrix(array $data): Matrix {
		return new self([$data]);
	}

	public static function createZero(int $rows, int $cols): Matrix {
		$cols = array_fill(0, $cols, 0.0);
		$rows = array_fill(0, $rows, []);
		$matrix = [];
		foreach ($rows as $r => $_) {
			foreach ($cols as $c => $v) {
				$matrix[$r][$c] = $v;
			}
		}
		return new static($matrix);
	}

	public function __construct(array $data) {
		if (!isset($data[0]) || !is_array($data[0])) {
			$data[0] = [];
		}
		foreach ($data as $r => $row) {
			if (!is_array($row)) {
				throw new InvalidArgumentException();
			}
			foreach ($row as $c => $column) {
				if (is_bool($column)) {
					$column = $column ? 1.0 : -1.0;
				}
				$this->matrix[$r][$c] = $column;
			}
		}
	}

	public function __toString() {
		return sprintf("[Matrix: rows=%d,cols=%d]", $this->getRows(), $this->getCols());
	}

	public function add(int $row, int $col, float $value) {
		$this->validate($row, $col)->setRowCol($row, $col, $this->matrix[$row][$col]+$value);
	}

	public function addMatrix(Matrix $other) {
		$source = $other->getData();
		for ($row = 0; $row < $this->getRows(); $row++) {
			for ($col = 0; $col < $this->getCols(); $col++) {
				$this->matrix[$row][$col] += $source[$row][$col] ?? 0;
			}
		}
	}

	public function clear() {
		for ($r = 0; $r < $this->getRows(); $r++) {
			for ($c = 0; $c < $this->getCols(); $c++) {
				$this->matrix[$r][$c] = 0.0;
			}
		}
	}

	public function equals($other, int $precision = Encog::DEFAULT_PRECISION): bool {
		if ($other instanceof self) {
			if ($other != $this) {
				if ($precision < 0) {
					throw new MatrixError("Precision can't be a negative number.");
				}
				if ($other->getRows() != $this->getRows() || $other->getCols() != $this->getCols()) {
					return false;
				}
				$test = 10.0 ** $precision;
				if (is_infinite($test) || $test > PHP_INT_MAX) {
					throw new MatrixError("Precision of $precision decimal places is not supported.");
				}
				$precision = Encog::DEFAULT_PRECISION ** $precision;
				$data = $other->getData();

				for ($r = 0; $r < $this->getRows(); $r++) {
					for ($c = 0; $c < $this->getCols(); $c++) {
						if ((int)($this->matrix[$r][$c] * $precision) != (int)($data[$r][$c] * $precision)) {
							return false;
						}
					}
				}
			}
			return true;
		}
		return false;
	}

	public function getMatrix(array $rows, int $start, int $end): Matrix {
		for ($i = 0, $d = []; $i < count($rows); $i++) {
			for ($j = $start; $j <= $end; $j++) {
				if (!isset($this->matrix[$rows[$i]][$j])) {
					throw new RangeException("Submatrix indices");
				}
				$d[$i][$j-$start] = $this->matrix[$rows[$i]][$j];
			}
		}
		return new Matrix($d);
	}

	public function multiply(float $value) {
		for ($r = 0; $r < $this->getRows(); $r++) {
			for ($c = 0; $c < $this->getCols(); $c++) {
				$this->matrix[$r][$c] *= $value;
			}
		}
	}

	public function multiplyVector(array $vector): array {
		$result = [];
		for ($r = 0; $r < $this->getRows(); $r++) {
			$result[$r] = 0;
			for ($c = 0; $c < $this->getCols(); $c++) {
				$result[$r] += $this->matrix[$r][$c] * $vector[$c];
			}
		}
		return $result;
	}

	public function randomize(float $min, float $max, Random $random = null) {
		for ($r = 0; $r < $this->getRows(); $r++) {
			for ($c = 0; $c < $this->getCols(); $c++) {
				$this->matrix[$r][$c] = RangeRandomizer::randomFloat($min, $max, $random);
			}
		}
	}

	public function get(int $row, int $col): float {
		return $this->validate($row, $col)->matrix[$row][$col];
	}

	public function set(float $value) {
		for ($r = 0; $r < $this->getRows(); $r++) {
			for ($c = 0; $c < $this->getCols(); $c++) {
				$this->matrix[$r][$c] = $value;
			}
		}
	}

	public function setRowCol(int $row, int $col, float $value) {
		$this->validate($row, $col)->matrix[$row][$col] = $value;
	}

	public function setFromMatrix(Matrix $m) {
		$source = $m->getData();
		for ($r = 0; $r < $this->getRows(); $r++) {
			for ($c = 0; $c < $this->getCols(); $c++) {
				$this->matrix[$r][$c] = $source[$r][$c] ?? 0;
			}
		}
	}

	public function getCols(): int {
		if (isset($this->matrix[0])) {
			return count($this->matrix[0]);
		}
		return 0;
	}

	public function getRows(): int {
		return count($this->matrix);
	}

	public function getRow(int $row): Matrix {
		if ($row > $this->getRows()) {
			throw new MatrixError("Row $row does not exist.");
		}
		for ($col = 0; $col < $this->getCols(); $col++) {
			$new[0][$col] = $this->matrix[$row][$col];
		}
		return new static($new ?? []);
	}

	public function getArrayCopy(): array {
		return $this->matrix;
	}

	public function &getData(): array {
		return $this->matrix;
	}

	public function hashCode(): int {
		return $this->sum() % PHP_INT_MAX;
	}

	public function inverse(): Matrix {
		return $this->solve(MatrixMath::identity($this->getRows()));
	}

	public function isSquare(): bool {
		return $this->getCols() == $this->getRows();
	}

	public function isVector(): bool {
		return $this->getRows() == 1 || $this->getCols() == 1;
	}

	public function isZero(): bool {
		for ($r = 0; $r < $this->getRows(); $r++) {
			for ($c = 0; $c < $this->getCols(); $c++) {
				if ($this->matrix[$r][$c] != 0) {
					return false;
				}
			}
		}
		return true;
	}

	public function size(): int {
		return $this->getRows() * $this->getCols();
	}

	public function solve(Matrix $m): Matrix {
		return $this->isSquare()
			? (new LUDecomposition($this))->solve($m)
			: (new QRDecomposition($this))->solve($m);
	}

	public function sum(): int {
		for ($s = 0, $r = 0; $r < $this->getRows(); $r++) {
			for ($c = 0; $c < $this->getCols(); $c++) {
				$s += $this->matrix[$r][$c];
			}
		}
		return $s;
	}

	public function toPackedArray(): array {
		for ($p = [], $r = 0; $r < $this->getRows(); $r++) {
			for ($c = 0; $c < $this->getCols(); $c++) {
				$p[] = $this->matrix[$r][$c];
			}
		}
		return $p;
	}

	public function fromPackedArray(array $data, int $index = 0) {
		for ($i = $index, $r = 0; $r < $this->getRows(); $r++) {
			for ($c = 0; $c < $this->getCols(); $c++) {
				$this->matrix[$r][$c] = $data[$i++];
			}
		}
	}

	private function validate(int $row, int $column) {
		if ($row < 0 || $row >= $this->getRows()) {
			throw new MatrixError("The row: $row is out of range: {$this->getRows()}");
		}
		if ($column < 0 || $column >= $this->getCols()) {
			throw new MatrixError("The column: $column is out of range: {$this->getCols()}");
		}
		return $this;
	}

	/** @var float[][] */
	private $matrix = [];
}
