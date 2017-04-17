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
namespace encog\neural\networks\training\strategy;

use encog\ml\train\MLTrain;
use encog\ml\train\strategy\Strategy;
use encog\neural\networks\training\Momentum;
use encog\neural\networks\training\TrainingError;
use encog\util\logging\EncogLogging;

/**
 * Attempt to automatically set a momentum in a training algorithm that supports
 * momentum.
 */
class SmartMomentum implements Strategy {
	const MIN_IMPROVEMENT   = 0.0001;
	const MAX_MOMENTUM      = 4;
	const START_MOMENTUM    = 0.1;
	const MOMENTUM_INCREASE = 0.01;
	const MOMENTUM_CYCLES   = 10;

	/** @var MLTrain */
	private $owner;

	/** @var Momentum  */
	private $setter;

	/** @var float */
	private $lastError;

	/** @var bool */
	private $ready = false;

	/** @var int */
	private $lastMomentum = 0;

	/** @var float */
	private $currentMomentum;

	public function init(MLTrain $train) {
		if (!$train instanceof Momentum) {
			throw new TrainingError("Trainer must implement Momentum.");
		}
		$this->owner = $train;
		$this->setter = $train;
		$this->setter->setMomentum(0.0);
		$this->currentMomentum = 0.0;
	}

	public function preIteration() {
		$this->lastError = $this->owner->getError();
	}

	public function postIteration() {
		if ($this->ready) {
			$currentError = $this->owner->getError();
			$lastImprovement = ($currentError-$this->lastError) / $this->lastError;
			EncogLogging::log(EncogLogging::LEVEL_DEBUG, "Last improvement: $lastImprovement");
			if ($lastImprovement > 0 || abs($lastImprovement) < self::MIN_IMPROVEMENT) {
				if (++$this->lastMomentum > self::MOMENTUM_CYCLES) {
					if ($this->currentMomentum == 0) {
						$this->currentMomentum = self::START_MOMENTUM;
					}
					$this->currentMomentum *= 1.0 + self::MOMENTUM_INCREASE;
					$this->setter->setMomentum($this->currentMomentum);
					EncogLogging::log(EncogLogging::LEVEL_DEBUG,
						"Adjusting momentum: {$this->currentMomentum}");

				}
			} else {
				EncogLogging::log(EncogLogging::LEVEL_DEBUG,
					"Setting momentum back to zero.");
				$this->setter->setMomentum(0.0);
				$this->currentMomentum = 0.0;
			}
		} else {
			$this->ready = true;
		}
	}
}
