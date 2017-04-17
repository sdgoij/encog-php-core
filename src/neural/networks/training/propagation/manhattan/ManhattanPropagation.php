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
namespace encog\neural\networks\training\propagation\manhattan;

use encog\EncogError;
use encog\ml\data\MLDataSet;
use encog\neural\networks\ContainsFlat;
use encog\neural\networks\training\LearningRate;
use encog\neural\networks\training\propagation\Propagation;
use encog\neural\networks\training\propagation\TrainingContinuation;

/**
 * One problem that the backpropagation technique has is that the magnitude of
 * the partial derivative may be calculated too large or too small. The
 * Manhattan update algorithm attempts to solve this by using the partial
 * derivative to only indicate the sign of the update to the weight matrix. The
 * actual amount added or subtracted from the weight matrix is obtained from a
 * simple constant. This constant must be adjusted based on the type of neural
 * network being trained. In general, start with a higher constant and decrease
 * it as needed.
 *
 * The Manhattan update algorithm can be thought of as a simplified version of
 * the resilient algorithm. The resilient algorithm uses more complex techniques
 * to determine the update value.
 */
class ManhattanPropagation extends Propagation implements LearningRate {
	public function __construct(ContainsFlat $network, MLDataSet $training, float $learningRate) {
		parent::__construct($network, $training);
		$this->learningRate = $learningRate;
		$this->zeroTolerance = 0.001;
	}

	public function getLearningRate(): float {
		return $this->learningRate;
	}

	public function setLearningRate(float $value) {
		$this->learningRate = $value;
	}

	public function canContinue(): bool {
		return false;
	}

	public function pause(): TrainingContinuation {
		throw new EncogError("This training type does not support training continue.");
	}

	public function resume(TrainingContinuation $state) {}

	public function updateWeight(array $gradients, array &$lastGradient, int $index, float $dropoutRate = 0.0): float {
		if ($dropoutRate > 0 && $this->dropoutRandomSource->nextDouble() < $dropoutRate) {
			return 0.0;
		}
		if (abs($gradients[$index]) < $this->zeroTolerance) {
			return 0.0;
		}
		if ($gradients[$index] > 0) {
			return $this->learningRate;
		}
		return -$this->learningRate;
	}

	public function setBatchSize(int $value) {
		if ($value != 0) {
			throw new EncogError("Online training is not supported for: " . __CLASS__);
		}
	}

	private $zeroTolerance;
	private $learningRate;
}
