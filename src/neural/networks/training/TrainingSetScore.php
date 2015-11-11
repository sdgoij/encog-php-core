<?php
/**
 * Copyright 2015-2016 Tim Jurcka <sdgoij@gmail.com>
 *
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
namespace encog\neural\networks\training;

use encog\ml\CalculateScore;
use encog\ml\data\MLDataSet;
use encog\ml\MLMethod;
use encog\ml\MLRegression;
use encog\util\error\CalculateRegressionError;

/**
 * Calculate a score based on a training set. This class allows simulated
 * annealing or genetic algorithms just as you would any other training set
 * based training method. The method must support regression (MLRegression).
 */
class TrainingSetScore implements CalculateScore {
	public function __construct(MLDataSet $training) {
		$this->training = $training;
	}

	public function calculateScore(MLMethod $method): float {
		if (!$method instanceof MLRegression) {
			throw new TrainingError("The method must support regression (MLRegression)");
		}
		return CalculateRegressionError::calculateError($method, $this->training);
	}

	public function shouldMinimize(): bool {
		return true;
	}

	public function requireSingleThreaded(): bool {
		return false;
	}

	/** @var MLDataSet */
	private $training;
}
