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
namespace encog\mathutil\randomize;

use encog\EncogError;
use encog\util\Random;

/**
 * Generate random choices unevenly. This class is used to select random
 * choices from a list, with a probability weight places on each item
 * in the list.
 *
 * This is often called a Roulette Wheel in Machine Learning texts. How it differs from
 * a Roulette Wheel that you might find in Las Vegas or Monte Carlo is that the
 * areas that can be selected are not of uniform size. However, you can be sure
 * that one will be picked.
 *
 * http://en.wikipedia.org/wiki/Fitness_proportionate_selection
 */
class RandomChoice {
	public function __construct(array $probabilities) {
		$this->probabilities = $probabilities;
		$this->length = count($probabilities);
		$total = array_sum($probabilities);

		if ($total != 0.0) {
			$factor = 1.0 / $total;
			$total2 = 0.0;

			for ($i = 0; $i < $this->length; $i++) {
				$this->probabilities[$i] = $this->probabilities[$i] * $factor;
				$total2 += $this->probabilities[$i];
			}
			if (abs(1.0 - $total2) > 0.02) {
				$this->setDefaultProbabilities(0, $this->length);
			}
		} else {
			$this->setDefaultProbabilities(0, $this->length);
		}
	}

	public function generate(Random $random): int {
		$r = $random->nextDouble();
		$sum = 0.0;
		for ($i = 0; $i < $this->length; $i++) {
			$sum += $this->probabilities[$i];
			if ($r < $sum) {
				return $i;
			}
		}
		for ($i = 0; $i < $this->length; $i++) {
			if ($this->probabilities[$i] != 0.0) {
				return $i;
			}
		}
		throw new EncogError("Invalid probabilities.");
	}

	public function generateSkip(Random $random, int $skip): int {
		$totalProp = 1.0 - $this->probabilities[$skip];
		$throwValue = $random->nextDouble() * $totalProp;
		$accumulator = 0.0;
		for ($i = 0; $i < $this->length; $i++) {
			if ($i != $skip) {
				$accumulator += $this->probabilities[$i];
				if ($accumulator > $throwValue) {
					return $i;
				}
			}
		}
		for ($i = 0; $i < $this->length; $i++) {
			if ($i != $skip && $this->probabilities[$i] != 0.0) {
				return $i;
			}
		}
		return -1;
	}

	private function setDefaultProbabilities($start, $end) {
		$this->probabilities = array_fill($start, $end, 1.0 / ($end-$start));
	}

	/** @var float[] */
	private $probabilities = [];
	/** @var int */
	private $length = 0;
}
