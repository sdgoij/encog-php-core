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
namespace encog\util;

use InvalidArgumentException;

class Random {

	const ADDEND     = 11;
	const MASK       = 281474976710655;
	const MULTIPLIER = 25214903917;

	/** @var int */
	private static $seedUniquifier = 8682522807148012;

	/** @var bool */
	private $haveNextNextGaussian;

	/** @var float */
	private $nextNextGaussian;

	/** @var int */
	private $seed;

	public function __construct(?int $seed = null) {
		if ($seed === null) {
			$seed = ++self::$seedUniquifier + time();
		}
		$this->setSeed($seed);
	}

	public function setSeed(int $seed) {
		$this->seed = ($seed ^ self::MULTIPLIER) & self::MASK;
		$this->haveNextNextGaussian = false;
	}

	protected function next(int $bits): int {
		$this->seed = ((int)((int)($this->seed * self::MULTIPLIER) + self::ADDEND)) & self::MASK;
		return Operator::urshift($this->seed, 48 - $bits);
	}

	public function nextBytes(array &$bytes) {
		foreach ($bytes as $k => $v) {
			for ($rand = $this->nextInt(), $n = min(count($bytes)-$k, 4); $n--> 0; $rand >>= 8) {
				$bytes[$k] = $rand % 256;
			}
		}
	}

	public function nextInt(?int $bound = null): int {
		return $bound ? $this->nextBoundInt($bound) : $this->next(32);
	}

	protected function nextBoundInt(int $bound): int {
		if ($bound <= 0) {
			throw new InvalidArgumentException("\$bound must be positive");
		}
		if (($bound & -$bound) == $bound) {
			return (($bound * $this->next(31)) >> 31);
		}
		do {
			$bits = $this->next(31);
			$val = $bits % $bound;
		} while ($bits - $val + ($bound-1) < 0);
		return $val;
	}

	public function nextLong(): int {
		return ($this->next(32) << 32) + $this->next(32);
	}

	public function nextBoolean(): bool {
		return $this->nextFloat() > .5;
	}

	public function nextFloat(): float {
		return $this->next(24) / (1 << 24);
	}

	public function nextDouble(): float {
		return (($this->next(26) << 27) + $this->next(27)) / (1 << 53);
	}

	public function nextGaussian(): float {
		if ($this->haveNextNextGaussian) {
			$this->haveNextNextGaussian = false;
			return $this->nextNextGaussian;
		}
		do {
			$v1 = 2 * $this->nextDouble() - 1;
			$v2 = 2 * $this->nextDouble() - 1;
			$s = $v1 * $v1 + $v2 * $v2;
		} while ($s >= 1 || $s == 0);
		$multiplier = sqrt(-2 * log($s) / $s);
		$this->nextNextGaussian = $v2 * $multiplier;
		$this->haveNextNextGaussian = true;
		return $v1 * $multiplier;
	}
}
