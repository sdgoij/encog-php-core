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
 * Provides the ability for subclasses to generate normally distributed random numbers.
 */
abstract class AbstractBoxMuller extends AbstractGenerateRandom {
	const MU    = 0;
	const SIGMA = 1;

	/** @var bool */
	private $useLast = false;

	/** @var int */
	private $y2;

	public function nextGaussian(): float {
		if (!$this->useLast) {
			do {
				$v1 = 2 * $this->nextDouble() - 1;
				$v2 = 2 * $this->nextDouble() - 1;
				$s = $v1 * $v1 + $v2 * $v2;
			} while ($s >= 1);
			$s = sqrt((-2.0 * log($s)) / $s);
			$y1 = $v1 * $s;
			$this->y2 = $v2 * $s;
			$this->useLast = true;
		} else {
			$this->useLast = false;
			$y1 = $this->y2;
		}
		return self::MU + $y1 * self::SIGMA;
	}
}
