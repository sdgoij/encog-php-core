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

use encog\util\Operator;
use InvalidArgumentException;
use SplFixedArray;

/**
 * The Mersenne twister is a pseudo random number generator developed in 1997 by Makato Matsumoto and
 * Takuji Nishimura that is based on a matrix linear recurrence over a finite binary field F2.
 *
 * References:
 *   http://en.wikipedia.org/wiki/Mersenne_twister/
 *   http://www.cs.gmu.edu/~sean/research
 *
 * Makato Matsumoto and Takuji Nishimura, "Mersenne Twister: A 623-Dimensionally Equidistributed Uniform
 * Pseudo-Random Number Generator", ACM Transactions on Modeling and. Computer Simulation,
 * Vol. 8, No. 1, January 1998, pp 3--30.
 */
class MersenneTwisterGenerateRandom extends AbstractBoxMuller {

	const TEMPERING_MASK_B = 0x9d2c5680;
	const TEMPERING_MASK_C = 0xefc60000;
	const MATRIX_A         = 0x9908b0df;
	const UPPER_MASK       = 0x80000000;
	const LOWER_MASK       = 0x7fffffff;
	const M                = 397;
	const N                = 624;

	/** @var int[2] */
	private $mag01 = [];

	/** @var int */
	private $mti;

	/** @var SplFixedArray */
	private $sv;

	/** @param int|int[] $seed */
	public function __construct($seed = null) {
		if ($seed === null) {
			$seed = time();
		}
		switch (true) {
			case is_array($seed):
				$this->setSeedArray($seed);
				break;
			case is_int($seed):
				$this->setSeed($seed);
				break;
			default:
				throw new InvalidArgumentException("Invalid seed type.");
		}
	}

	public function setSeed(int $seed) {
		$this->sv = new SplFixedArray(self::N);
		$this->mag01 = [0x0, self::MATRIX_A];
		$this->sv[0] = $seed;

		for ($this->mti = 1; $this->mti < self::N; $this->mti++) {
			$this->sv[$this->mti] = (int)(1812433253 * ($this->sv[$this->mti-1] ^
				Operator::urshift($this->sv[$this->mti-1], 30)) + $this->mti);
		}
	}

	public function setSeedArray(array $seeds) {
		$length = count($seeds);
		$this->setSeed(19650218);
		for ($i = 1, $j = 0, $k = (self::N > $length ? self::N : $length)-1; $k > 0; $k--) {
			$this->sv[$i] = (int)((int)$this->sv[$i]^(($this->sv[$i-1]^(int)(Operator::urshift($this->sv[$i-1],30))*1664525)))+$seeds[$j]+$j;
			if (++$i >= self::N) {
				$this->sv[0] = $this->sv[self::N-1];
				$i = 1;
			}
			if (++$j >= $length-1) $j = 0;
		}
		for ($k = self::N-1; $k > 0; $k--) {
			$this->sv[$i] = (int)(((int)$this->sv[$i])^(int)(((int)($this->sv[$i-1]^Operator::urshift($this->sv[$i-1],30))*1566083941)))-$i;
			if (++$i >= self::N) {
				$this->sv[0] = $this->sv[self::N-1];
				$i = 1;
			}
		}
		$this->sv[0] = self::UPPER_MASK;
	}

	public function nextLong(): int {
		return ($this->nextI(32) << 32) | $this->nextI(32);
	}

	protected function next(): float {
		return (((int)$this->nextI(26) << 27) + $this->nextI(27)) / (float)(1 << 53);
	}

	private function nextI(int $bits): int {
		if ($this->mti >= self::N) {
			for ($i = 0; $i < self::N-self::M; $i++) {
				$y = ($this->sv[$i] & self::UPPER_MASK) | ($this->sv[$i+1] & self::LOWER_MASK);
				$this->sv[$i] = $this->sv[$i+self::M] ^ Operator::urshift($y, 1) ^ $this->mag01[$y&0x1];
			}
			for (; $i < self::N-1; $i++) {
				$y = ($this->sv[$i] & self::UPPER_MASK) | ($this->sv[$i+1] & self::LOWER_MASK);
				$this->sv[$i] = $this->sv[$i+(self::M-self::N)] ^ Operator::urshift($y, 1) ^ $this->mag01[$y&0x1];
			}
			$y = ($this->sv[self::N-1] & self::UPPER_MASK) | ($this->sv[0] & self::LOWER_MASK);
			$this->sv[self::N-1] = $this->sv[self::M-1] ^ Operator::urshift($y, 1) ^ $this->mag01[$y&0x1];
			$this->mti = 0;
		}
		$y = $this->sv[$this->mti++];
		$y ^= Operator::urshift($y, 11);
		$y ^= $y << 7 & self::TEMPERING_MASK_B;
		$y ^= $y << 15 & self::TEMPERING_MASK_C;
		$y ^= Operator::urshift($y, 18);
		return Operator::urshift($y, 32-$bits);
	}
}
