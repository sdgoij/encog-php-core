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
use encog\neural\networks\training\LearningRate;
use encog\neural\networks\training\TrainingError;
use encog\util\logging\EncogLogging;

/**
 * Attempt to automatically set the learning rate in a learning method that
 * supports a learning rate.
 */
class SmartLearningRate implements Strategy {
	const LEARNING_DECAY = 0.99;

	/** @var MLTrain|LearningRate */
	private $trainer;

	/** @var float */
	private $currentLearningRate;

	/** @var float */
	private $lastError;

	/** @var bool */
	private $ready = false;

	public function init(MLTrain $trainer) {
		if (!$trainer instanceof LearningRate) {
			throw new TrainingError("Trainer must implement LearningRate.");
		}
		$this->currentLearningRate = 1.0 / $trainer->getTraining()->getRecordCount();
		$trainer->setLearningRate($this->currentLearningRate);
		$this->trainer = $trainer;

		EncogLogging::log(EncogLogging::LEVEL_DEBUG,
			"Starting learning rate: {$this->currentLearningRate}");
	}

	public function preIteration() {
		$this->lastError = $this->trainer->getError();
	}

	public function postIteration() {
		if ($this->ready) {
			if ($this->trainer->getError() > $this->lastError) {
				$this->currentLearningRate *= self::LEARNING_DECAY;
				$this->trainer->setLearningRate($this->currentLearningRate);

				EncogLogging::log(EncogLogging::LEVEL_DEBUG,
					"Adjusting learning rate to {$this->currentLearningRate}");
			}
		} else {
			$this->ready = true;
		}
	}
}
