<?php
declare(strict_types=1);
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
namespace encog\mathutil\randomize\generate;

/**
 * A Linear Congruential random number generator. A Linear Congruential Generator (LCG) yields a sequence of
 * randomized numbers calculated with a linear equation. The method represents one of the oldest and best-known
 * pseudo random number generator algorithms. Most programming languages use this technique.
 *
 * http://en.wikipedia.org/wiki/Linear_congruential_generator/
 * Donald Knuth, The Art of Computer Programming, Volume 3, Section 3.2.1
 */
class LinearCongruentialRandom extends AbstractBoxMuller {
	const DEFAULT_MOD1 = 2;
	const DEFAULT_MOD2 = 32;
	const DEFAULT_MULT = 1103515245;
	const DEFAULT_INC  = 12345;
	const MAX_RAND     = 4294967295;

	private $modulus;
	private $multiplier;
	private $increment;
	private $seed;

	public function __construct(?int $seed = null, ?int $modulus = null,
			int $multiplier = self::DEFAULT_MULT, int $increment = self::DEFAULT_INC) {
		if (null === $modulus) {
			$modulus = pow(self::DEFAULT_MOD1, self::DEFAULT_MOD2);
		}
		if (null === $seed) {
			$seed = time();
		}
		$this->modulus = $modulus;
		$this->multiplier = $multiplier;
		$this->increment = $increment;
		$this->seed = $seed % self::MAX_RAND;
	}

	public function getModulus(): int {
		return $this->modulus;
	}

	public function getMultiplier(): int {
		return $this->multiplier;
	}

	public function getIncrement(): int {
		return $this->increment;
	}

	public function getSeed(): int {
		return $this->seed;
	}

	protected function next(): float {
		$this->seed = ($this->multiplier * $this->seed + $this->increment) % $this->modulus;
		return ((float)$this->seed) / self::MAX_RAND;
	}
}
