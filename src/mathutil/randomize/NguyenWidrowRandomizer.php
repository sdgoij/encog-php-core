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
use encog\engine\network\activation\ActivationFunction;
use encog\mathutil\matrices\Matrix;
use encog\ml\MLMethod;
use encog\neural\networks\BasicNetwork;
use SplFixedArray;

/**
 * Implementation of Nguyen-Widrow weight initialization. This is the default weight initialization
 * used by Encog, as it generally provides the most train-able neural network.
 */
class NguyenWidrowRandomizer extends BasicRandomizer {

	public function randomize(MLMethod $method) {
		if (!$method instanceof BasicNetwork) {
			throw new EncogError("Nguyen-Widrow only supports BasicNetwork.");
		}
		for ($i = 0; $i < $method->getLayerCount()-1; $i++) {
			$this->randomizeLayer($method, $i);
		}
	}

	public function randomizeLayer(BasicNetwork $network, int $layer) {
		$toLayer = $layer+1;
		$toCount = $network->getLayerNeuronCount($toLayer);
		$fromCount = $network->getLayerNeuronCount($layer);
		$fromCountTotalCount = $network->getLayerTotalNeuronCount($layer);
		$activation = $network->getActivation($toLayer);
		$high = $this->calculateRange($activation, INF);
		$low = $this->calculateRange($activation, -INF);
		$b = .7 * pow($toCount, 1/$fromCount) / ($high-$low);

		for ($toNeuron = 0; $toNeuron < $toCount; $toNeuron++) {
			if ($fromCount != $fromCountTotalCount) {
				$network->setWeight($layer, $fromCount, $toNeuron, $this->nextDoubleRange(-$b, $b));
			}
			for ($fromNeuron = 0; $fromNeuron < $fromCount; $fromNeuron++) {
				$network->setWeight($layer, $fromNeuron, $toNeuron, $this->nextDoubleRange(0, $b));
			}
		}
	}

	private function calculateRange(ActivationFunction $af, float $value): float {
		$values = SplFixedArray::fromArray([$value]);
		$af->activationFunction($values, 0, 1);
		return $values[0];
	}

	const MESSAGE = "This type of randomization is not supported by Nguyen-Widrow";

	public function randomizeArray(array &$values, int $start = 0, int $size = null) {
		throw new EncogError(self::MESSAGE);
	}

	public function randomizeArray2D(array &$values) {
		throw new EncogError(self::MESSAGE);
	}

	public function randomizeFloat(float $value): float {
		throw new EncogError(self::MESSAGE);
	}

	public function randomizeMatrix(Matrix $m) {
		throw new EncogError(self::MESSAGE);
	}
}
