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

use encog\ml\MLEncodable;
use encog\ml\train\MLTrain;
use encog\neural\networks\training\TrainingError;
use encog\util\logging\EncogLogging;

/**
 * A simple greedy strategy. If the last iteration did not improve training,
 * then discard it. Care must be taken with this strategy, as sometimes a
 * training algorithm may need to temporarily decrease the error level before
 * improving it.
 */
class Greedy implements Strategy {
	public function init(MLTrain $trainer) {
		if (!$trainer->getMethod() instanceof MLEncodable) {
			throw new TrainingError("To make use of the Greedy strategy the machine learning method must support MLEncodable.");
		}
		$this->ready = false;
		$this->trainer = $trainer;
		$this->method = $trainer->getMethod();
		$this->weights = [];
	}

	public function preIteration() {
		$this->error = $this->trainer->getError();
		$this->method->encodeToArray($this->weights);
		$this->trainer->setError($this->error);
	}

	public function postIteration() {
		if ($this->ready) {
			$err =$this->trainer->getError();
			if ($err > $this->error) {
				EncogLogging::log(EncogLogging::LEVEL_DEBUG, "Greedy strategy dropped last iteration.");
				$this->method->decodeFromArray($this->weights);
				$this->trainer->setError($this->error);
			}
		} else {
			$this->ready = true;
		}
	}

	/** @var MLTrain */
	private $trainer;

	/** @var float */
	private $error;

	/** @var float[] */
	private $weights = [];

	/** @var bool */
	private $ready = false;

	/** @var MLEncodable */
	private $method;
}
