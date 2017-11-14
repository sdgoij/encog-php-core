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
namespace encog\neural\som;

use encog\mathutil\matrices\Matrix;
use encog\ml\data\MLData;
use encog\ml\data\MLDataPair;
use encog\ml\data\MLDataSet;
use encog\ml\MLClassification;
use encog\ml\MLError;
use encog\ml\MLResettable;
use encog\neural\NeuralNetworkError;
use encog\neural\som\training\basic\BestMatchingUnit;
use encog\util\Random;

/**
 * A self organizing map neural network.
 */
class SOM implements MLClassification, MLError, MLResettable {
	public function __construct(int $inputs, int $outputs) {
		$this->weights = Matrix::createZero($outputs, $inputs);
	}

	public function classify(MLData $input): int {
		if ($input->size() > $this->getInputCount()) {
			throw new NeuralNetworkError(
				"Can't classify SOM with input size of {$this->getInputCount()} with input data of size {$input->size()}"
			);
		}
		$m = $this->weights->getData();
		$data = $input->getData();
		$minDist = INF;
		$result = -1;

		for ($i = 0, $o = $this->getInputCount(); $i < $o; $i++) {
			$distance = self::euclideanDistance($data, $m[$i]);
			if ($distance < $minDist) {
				$minDist = $distance;
				$result = $i;
			}
		}
		return $result;
	}

	public function calculateError(MLDataSet $data): float {
		$bmu = new BestMatchingUnit($this);
		$bmu->reset();
		/** @var MLDataPair $pair */
		foreach ($data as $pair) {
			$bmu->calculateBMU($pair->getInput());
		}
		return $bmu->getWorstDistance() / 100.0;
	}

	public function getInputCount(): int {
		return $this->weights->getCols();
	}

	public function getOutputCount(): int {
		return $this->weights->getRows();
	}

	public function reset(int $seed = null) {
		$this->weights->randomize(-1.0, 1.0, $seed ? new Random($seed) : null);
	}

	public function getWeights(): Matrix {
		return $this->weights;
	}

	public function setWeights(Matrix $weights) {
		$this->weights = $weights;
	}

	public function winner(MLData $input): int {
		return $this->classify($input);
	}

	private static function euclideanDistance($p1, $p2): float {
		for ($i = 0, $sum = 0.0; $i < count($p1); $i++) {
			$delta = $p1[$i] - $p2[$i];
			$sum += $delta * $delta;
		}
		return sqrt($sum);
	}

	/** @var Matrix */
	private $weights;
}
