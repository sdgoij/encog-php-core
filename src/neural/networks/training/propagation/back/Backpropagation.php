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
namespace encog\neural\networks\training\propagation\back;

use encog\ml\data\MLDataSet;
use encog\neural\networks\ContainsFlat;
use encog\neural\networks\training\LearningRate;
use encog\neural\networks\training\Momentum;
use encog\neural\networks\training\propagation\Propagation;
use encog\neural\networks\training\propagation\TrainingContinuation;
use encog\neural\networks\training\strategy\SmartLearningRate;
use encog\neural\networks\training\strategy\SmartMomentum;
use encog\neural\networks\training\TrainingError;

/**
 * This class implements a backpropagation training algorithm for feed forward
 * neural networks. It is used in the same manner as any other training class
 * that implements the Train interface.
 *
 * Backpropagation is a common neural network training algorithm. It works by
 * analyzing the error of the output of the neural network. Each neuron in the
 * output layer's contribution, according to weight, to this error is
 * determined. These weights are then adjusted to minimize this error. This
 * process continues working its way backwards through the layers of the neural
 * network.
 *
 * This implementation of the backpropagation algorithm uses both momentum and a
 * learning rate. The learning rate specifies the degree to which the weight
 * matrices will be modified through each iteration. The momentum specifies how
 * much the previous learning iteration affects the current. To use no momentum
 * at all specify zero.
 *
 * One primary problem with backpropagation is that the magnitude of the partial
 * derivative is often detrimental to the training of the neural network. The
 * other propagation methods of Manhattan and Resilient address this issue in
 * different ways. In general, it is suggested that you use the resilient
 * propagation technique for most Encog training tasks over back propagation.
 */
class Backpropagation extends Propagation implements LearningRate, Momentum {
	const LAST_DELTA = "LAST_DELTA";

	/** @var float */
	private $learningRate;

	/** @var float */
	private $momentum;

	/** @var float[] */
	private $lastDelta = [];

	public function __construct(ContainsFlat $network, MLDataSet $training,
			float $learningRate = 0, float $momentum = 0) {
		parent::__construct($network, $training);
		if (!$learningRate || !$momentum) {
			$this->addStrategy(new SmartLearningRate());
			$this->addStrategy(new SmartMomentum());
		}
		$this->lastDelta = array_fill(0, count($network->getFlat()->getWeights()), 0.0);
		$this->learningRate = $learningRate;
		$this->momentum = $momentum;
	}

	public function &getLastDelta(): array {
		return $this->lastDelta;
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

	public function isValidResume(TrainingContinuation $state): bool {
		if (!array_key_exists(self::LAST_DELTA, $state->getContents()) ||
				$state->getTrainingType() != __CLASS__) {
			return false;
		}
		$method = $this->getMethod();
		if (!$method instanceof ContainsFlat) {
			return false;
		}
		return count($state->get(self::LAST_DELTA)) == count($method->getFlat()->getWeights());
	}

	public function pause(): TrainingContinuation {
		$state = new TrainingContinuation();
		$state->setTrainingType(__CLASS__);
		$state->set(self::LAST_DELTA, $this->lastDelta);
		return $state;
	}

	public function resume(TrainingContinuation $state) {
		if (!$this->isValidResume($state)) {
			throw new TrainingError("Invalid training resume data length");
		}
		$this->lastDelta = (array)$state->get(self::LAST_DELTA);
	}

	public function setMomentum(float $value) {
		$this->momentum = $value;
	}

	public function getMomentum(): float {
		return $this->momentum;
	}

	public function updateWeight(
		array $gradients,
		array &$lastGradient,
		int $index,
		float $dropoutRate = 0.0
	): float {
		if ($dropoutRate > 0 && $this->dropoutRandomSource->nextDouble() < $dropoutRate) {
			return 0.0;
		}
		$delta = ($gradients[$index]*$this->learningRate) + ($this->lastDelta[$index]*$this->momentum);
		$this->lastDelta[$index] = $delta;
		return $delta;
	}
}
