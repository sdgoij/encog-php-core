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
namespace encog\ml\train\strategy;

use encog\ml\train\MLTrain;
use encog\util\logging\EncogLogging;

/**
 * A hybrid strategy allows a secondary training algorithm to be used. Once the
 * primary algorithm is no longer improving by much, the secondary will be used.
 * Using simulated annealing in as a secondary to one of the propagation methods
 * is often a very efficient combination as it can help the propagation method
 * escape a local minimum. This is particularly true with backpropagation.
 */
class Hybrid implements Strategy {
	const DEFAULT_MIN_IMPROVEMENT = 0.00001;
	const DEFAULT_TOLERATE_CYCLES = 10;
	const DEFAULT_ALTERNATE_CYCLES = 5;

	public function __construct(MLTrain $secondary,
			float $minImprovement = self::DEFAULT_MIN_IMPROVEMENT,
			int $tolerateMinImprovement = self::DEFAULT_TOLERATE_CYCLES,
			int $alternateCycles = self::DEFAULT_ALTERNATE_CYCLES
		) {
		$this->secondary = $secondary;
		$this->minImprovement = $minImprovement;
		$this->tolerateMinImprovement = $tolerateMinImprovement;
		$this->alternateCycles = $alternateCycles;
		$this->lastHybrid = 0;
		$this->ready = false;
	}

	public function init(MLTrain $trainer) {
		$this->primary = $trainer;
	}

	public function preIteration() {
		$this->lastError = $this->primary->getError();
	}

	public function postIteration() {
		if ($this->ready) {
			$this->lastImprovement = ($this->primary->getError() - $this->lastError) / $this->lastError;
			EncogLogging::log(EncogLogging::LEVEL_DEBUG, "Last improvement: {$this->lastImprovement}");
			if ($this->lastImprovement > 0 || abs($this->lastImprovement) < $this->minImprovement) {
				if (++$this->lastHybrid > $this->tolerateMinImprovement) {
					EncogLogging::log(EncogLogging::LEVEL_DEBUG, "Performing hybrid cycle");
					for ($i = 0; $i < $this->alternateCycles; $i++) {
						$this->secondary->iteration();
					}
					$this->lastHybrid = 0;
				}
			}
		} else {
			$this->ready = true;
		}
	}

	/** @var MLTrain */
	private $primary, $secondary;

	/** @var float */
	private $lastImprovement, $lastError;

	/** @var bool */
	private $ready = false;

	/** @var int */
	private $lastHybrid;

	/** @var float */
	private $minImprovement;

	/** @var float */
	private $tolerateMinImprovement;

	/** @var int */
	private $alternateCycles;
}
