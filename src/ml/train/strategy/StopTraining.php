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
namespace encog\ml\train\strategy;

use encog\ml\train\MLTrain;

/**
 * This strategy will indicate once training is no longer improving the neural
 * network by a specified amount, over a specified number of cycles. This allows
 * the program to automatically determine when to stop training.
 */
class StopTraining implements EndTraining {
	const DEFAULT_MIN_IMPROVEMENT = 0.0000001;
	const DEFAULT_TOLERATE_CYCLES = 100;

	public function __construct(float $improvement = self::DEFAULT_MIN_IMPROVEMENT,
			int $cycles = self::DEFAULT_TOLERATE_CYCLES) {
		$this->minImprovement = $improvement;
		$this->toleratedCycles = $cycles;
		$this->bestError = INF;
		$this->badCycles = 0;
	}

	public function shouldStop(): bool {
		return $this->shouldStop;
	}

	public function init(MLTrain $trainer) {
		$this->trainer = $trainer;
		$this->shouldStop = false;
		$this->ready = false;
	}

	public function preIteration() {}

	public function postIteration() {
		if ($this->ready) {
			if (abs($this->bestError-$this->trainer->getError()) < $this->minImprovement) {
				if (++$this->badCycles > $this->toleratedCycles) {
					$this->shouldStop = true;
				}
			} else {
				$this->badCycles = 0;
			}
		} else {
			$this->ready = true;
		}
		$this->lastError = $this->trainer->getError();
		$this->bestError = min($this->lastError, $this->bestError);
	}

	/** @var MLTrain */
	private $trainer;

	/** @var bool */
	private $shouldStop = false;

	/** @var bool */
	private $ready = false;

	/** @var float */
	private $lastError, $bestError;

	/** @var float */
	private $minImprovement;

	/** @var int */
	private $toleratedCycles;

	/** @var int */
	private $badCycles;
}
