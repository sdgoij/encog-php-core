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
namespace encog\mathutil;

/**
 * A complex number class.
 *
 * This class is based on source code by
 *   Andrew G. Bennett, Department of Mathematics Kansas State University
 *
 * The original version can be found here:
 *   http://www.math.ksu.edu/~bennett/jomacg/c.html
 */
class ComplexNumber {
	public function __construct(float $x, float $y) {
		$this->x = $x;
		$this->y = $y;
	}

	public function __toString() {
		if ($this->x != 0 && $this->y > 0) {
			return $this->x . " + " . $this->y . "i";
		}
		if ($this->x != 0 && $this->y < 0) {
			return $this->x . " - " . (-$this->y) . "i";
		}
		if ($this->y == 0) {
			return "".$this->x;
		}
		if ($this->x == 0) {
			return $this->y . "i";
		}
		return $this->x . " + i*" . $this->y;
	}

	public static function copy(ComplexNumber $other): ComplexNumber {
		return new ComplexNumber($other->getReal(), $other->getImaginary());
	}

	public function getReal(): float {
		return $this->x;
	}

	public function getImaginary(): float {
		return $this->y;
	}

	public function mod(): float {
		if ($this->x != 0 || $this->y != 0) {
			return sqrt($this->x * $this->x + $this->y * $this->y);
		}
		return 0.0;
	}

	public function arg(): float {
		return atan2($this->y, $this->x);
	}

	public function conj(): ComplexNumber {
		return new ComplexNumber($this->x, -$this->y);
	}

	public function plus(ComplexNumber $n): ComplexNumber {
		return new ComplexNumber($this->x + $n->getReal(), $this->y + $n->getImaginary());
	}

	public function minus(ComplexNumber $n): ComplexNumber {
		return new ComplexNumber($this->x - $n->getReal(), $this->y - $n->getImaginary());
	}

	public function times(ComplexNumber $n): ComplexNumber {
		return new ComplexNumber(
			$this->x * $n->getReal() - $this->y * $n->getImaginary(),
			$this->x * $n->getImaginary() + $this->y * $n->getReal()
		);
	}

	public function div(ComplexNumber $n): ComplexNumber {
		$den = pow($n->mod(), 2);
		return new ComplexNumber(
			($this->x * $n->getReal() + $this->y * $n->getImaginary()) / $den,
			($this->y * $n->getReal() - $this->x * $n->getImaginary()) / $den
		);
	}

	public function exp(): ComplexNumber {
		return new ComplexNumber(
			exp($this->x) * cos($this->y),
			exp($this->x) * sin($this->y)
		);
	}

	public function log(): ComplexNumber {
		return new ComplexNumber(log($this->mod()), $this->arg());
	}

	public function sqrt(): ComplexNumber {
		$r = sqrt($this->mod());
		$ta = $this->arg() / 2;
		return new ComplexNumber(
			$r * cos($ta),
			$r * sin($ta)
		);
	}

	public function sin(): ComplexNumber {
		return new ComplexNumber(
			$this->_cosh($this->y) * sin($this->x),
			$this->_sinh($this->y) * cos($this->x)
		);
	}

	public function cos(): ComplexNumber {
		return new ComplexNumber(
			$this->_cosh($this->y) * cos($this->x),
			-$this->_sinh($this->y) * sin($this->x)
		);
	}

	public function sinh(): ComplexNumber {
		return new ComplexNumber(
			$this->_sinh($this->x) * cos($this->y),
			$this->_cosh($this->x) * sin($this->y)
		);
	}

	public function cosh(): ComplexNumber {
		return new ComplexNumber(
			$this->_cosh($this->x) * cos($this->y),
			$this->_sinh($this->x) * sin($this->y)
		);
	}

	public function tan(): ComplexNumber {
		return $this->sin()->div($this->cos());
	}

	public function chs(): ComplexNumber {
		return new ComplexNumber(-$this->x, -$this->y);
	}

	private function _cosh(float $ta): float {
		return (exp($ta) + exp(-$ta)) / 2;
	}

	private function _sinh(float $ta): float {
		return (exp($ta) - exp(-$ta)) / 2;
	}

	/** @var float */
	private $x, $y;
}
