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
namespace encog\neural\som\training\basic;

use encog\mathutil\BoundMath;
use encog\mathutil\matrices\Matrix;
use encog\ml\data\MLData;
use encog\neural\NeuralNetworkError;
use encog\neural\som\SOM;

/**
 * The "Best Matching Unit" or BMU is a very important concept in the training
 * for a SOM. The BMU is the output neuron that has weight connections to the
 * input neurons that most closely match the current input vector. This neuron
 * (and its "neighborhood") are the neurons that will receive training.
 *
 * This class also tracks the worst distance (of all BMU's). This gives some
 * indication of how well the network is trained, and thus becomes the "error"
 * of the entire network.
 */
class BestMatchingUnit {
	public function __construct(SOM $som) {
		$this->som = $som;
	}

	public function calculateBMU(MLData $input): int {
		$result = 0;

		if ($input->size() > $this->som->getInputCount()) {
			throw new NeuralNetworkError(
				"Can't train SOM with input size of {$this->som->getInputCount()} with input data of size {$input->size()}");
		}
		$lowestDistance = INF;
		$outputSize = $this->som->getOutputCount();
		for ($i = 0; $i < $outputSize; $i++) {
			$distance = $this->calculateEuclideanDistance($this->som->getWeights(), $input, $i);
			if ($distance < $lowestDistance) {
				$lowestDistance = $distance;
				$result = $i;
			}
		}
		if ($lowestDistance > $this->worstDistance) {
			$this->worstDistance = $lowestDistance;
		}
		return $result;
	}

	public function calculateEuclideanDistance(Matrix $matrix, MLData $input, int $output): float {
		$result = 0.0;
		$size = $input->size();
		for ($i = 0; $i < $size; $i++) {
			$diff = $input->getDataAt($i) - $matrix->get($output, $i);
			$result += $diff * $diff;
		}
		return BoundMath::sqrt($result);
	}

	public function getWorstDistance(): float {
		return $this->worstDistance;
	}

	public function reset() {
		$this->worstDistance = 0.0;
	}

	/** @var float */
	private $worstDistance = 0.0;

	/** @var SOM */
	private $som;
}
