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
namespace encog\neural\networks\training\propagation\resilient;

use encog\mathutil\EncogMath;
use encog\ml\data\MLDataSet;
use encog\neural\networks\ContainsFlat;
use encog\neural\networks\training\propagation\Propagation;
use encog\neural\networks\training\propagation\TrainingContinuation;
use encog\neural\networks\training\TrainingError;
use SplFixedArray;

/**
 * One problem with the backpropagation algorithm is that the magnitude of the
 * partial derivative is usually too large or too small. Further, the learning
 * rate is a single value for the entire neural network. The resilient
 * propagation learning algorithm uses a special update value(similar to the
 * learning rate) for every neuron connection. Further these update values are
 * automatically determined, unlike the learning rate of the backpropagation
 * algorithm.
 *
 * For most training situations, we suggest that the resilient propagation
 * algorithm (this class) be used for training.
 *
 * There are a total of three parameters that must be provided to the resilient
 * training algorithm. Defaults are provided for each, and in nearly all cases,
 * these defaults are acceptable. This makes the resilient propagation algorithm
 * one of the easiest and most efficient training algorithms available.
 *
 * It is also important to note that RPROP does not work well with online training.
 * You should always use a batch size bigger than one.  Typically the larger the better.
 * By default a batch size of zero is used, zero means to include the entire training
 * set in the batch.
 *
 * The optional parameters are:
 *
 *   initialUpdate - What are the initial update values for each matrix value. The
 * default is 0.1.
 *
 *   maxStep - What is the largest amount that the update values can step. The
 * default is 50.
 *
 * Usually you will not need to use these.
 */
class ResilientPropagation extends Propagation {
	/** Continuation tag for the last gradients. */
	const LAST_GRADIENTS = "LAST_GRADIENTS";
	/** Continuation tag for the last values. */
	const UPDATE_VALUES = "UPDATE_VALUES";

	/** @var int */
	private static $Q = 1;

	/** @var SplFixedArray */
	private $updateValues;
	/** @var SplFixedArray */
	private $lastDelta;
	/** @var float */
	private $zeroTolerance;
	/** @var float */
	private $maxStep;
	/** @var RPROPType */
	private $type;
	/** @var SplFixedArray */
	private $lastWeightChange;
	/** @var float */
	private $lastError = INF;

	public function __construct(ContainsFlat $network, MLDataSet $training,
			float $initialUpdate = RPROPConst::DEFAULT_INITIAL_UPDATE,
			float $maxStep = RPROPConst::DEFAULT_MAX_STEP
		) {
		parent::__construct($network, $training);
		$numWeights = count($network->getFlat()->getWeights());
		$this->lastDelta = new SplFixedArray($numWeights);
		$this->lastWeightChange = new SplFixedArray($numWeights);
		$this->updateValues = new SplFixedArray($numWeights);
		$this->zeroTolerance = RPROPConst::DEFAULT_ZERO_TOLERANCE;
		$this->maxStep = $maxStep;

		foreach ($this->updateValues as $key => $value) {
			$this->updateValues[$key] = $initialUpdate;
			$this->lastDelta[$key] = 0.0;
		}
		$this->type = RPROPType::$RPROPp;
	}

	public function canContinue(): bool {
		return true;
	}

	public function isValidResume(TrainingContinuation $state): bool {
		$contents = $state->getContents();
		$method = $this->getMethod();
		if (!$method instanceof ContainsFlat) {
			throw new TrainingError("Unsupported ML method.");
		}
		if (!array_key_exists(self::LAST_GRADIENTS, $contents) ||
				!array_key_exists(self::UPDATE_VALUES, $contents) ||
				$state->getTrainingType() != __CLASS__
		) {
			return false;
		}
		return count($state->get(self::LAST_GRADIENTS)) == count($method->getFlat()->getWeights());
	}

	public function pause(): TrainingContinuation {
		$result = new TrainingContinuation();
		$result->setTrainingType(__CLASS__);
		$result->set(self::LAST_GRADIENTS, $this->getLastGradient());
		$result->set(self::UPDATE_VALUES, $this->getUpdateValues());
		return $result;
	}

	public function resume(TrainingContinuation $state) {
		if (!$this->isValidResume($state)) {
			throw new TrainingError("Invalid training resume data length");
		}
		$this->setLastGradient($state->get(self::LAST_GRADIENTS));
		$this->setUpdateValues($state->get(self::UPDATE_VALUES));
	}

	public function updateWeight(array $gradients, array &$lastGradient, int $index, float $dropoutRate = 0.0): float {
		if ($dropoutRate > 0 && $this->dropoutRandomSource->nextDouble() > $dropoutRate) {
			return 0.0;
		}
		do {
			if ($this->type == RPROPType::$RPROPp) {
				$weight = $this->updateWeightPlus($gradients, $lastGradient, $index);
				break;
			}
			if ($this->type == RPROPType::$RPROPm) {
				$weight = $this->updateWeightMinus($gradients, $lastGradient, $index);
				break;
			}
			if ($this->type == RPROPType::$iRPROPp) {
				$weight = $this->updateIWeightPlus($gradients, $lastGradient, $index);
				break;
			}
			if ($this->type == RPROPType::$iRPROPm) {
				$weight = $this->updateIWeightMinus($gradients, $lastGradient, $index);
				break;
			}
			if ($this->type == RPROPType::$ARPROP) {
				$weight = $this->updateJacobiWeight($gradients, $lastGradient, $index);
				break;
			}
			throw new TrainingError("Unknown RPROP type: {$this->type}");
		} while (0);

		$this->lastWeightChange[$index] = $weight;
		return $weight;
	}

	public function updateWeightPlus(array $gradients, array &$lastGradient, int $index): float {
		$change = ($gradients[$index] * $lastGradient[$index]) <=> 0;
		$weight = 0.0;

		if ($change > 0) {
			$delta = min($this->updateValues[$index] * RPROPConst::POSITIVE_ETA, $this->maxStep);
			$weight = ($gradients[$index] <=> 0) * $delta;
			$this->updateValues[$index] = $delta;
			$lastGradient[$index] = $gradients[$index];
		} else if ($change < 0) {
			$delta = max($this->updateValues[$index] * RPROPConst::NEGATIVE_ETA, RPROPConst::DELTA_MIN);
			$this->updateValues[$index] = $delta;
			$weight = -$this->lastWeightChange[$index];
			$lastGradient[$index] = 0.0;
		} else if ($change == 0) {
			$weight = ($gradients[$index] <=> 0) * $this->updateValues[$index];
			$lastGradient[$index] = $gradients[$index];
		}
		return $weight;
	}

	public function updateWeightMinus(array $gradients, array &$lastGradient, int $index): float {
		$change = ($gradients[$index] * $lastGradient[$index]) <=> 0;
		$delta  = ($change > 0)
			? min($this->lastDelta[$index] * RPROPConst::POSITIVE_ETA, $this->maxStep)
			: max($this->lastDelta[$index] * RPROPConst::NEGATIVE_ETA, RPROPConst::DELTA_MIN);
		$lastGradient[$index] = $gradients[$index];
		$this->lastDelta[$index] = $delta;
		return ($gradients[$index] <=> 0) * $delta;
	}

	public function updateIWeightPlus(array $gradients, array &$lastGradient, int $index): float {
		$change = ($gradients[$index] * $lastGradient[$index]) <=> 0;
		$weight = 0.0;

		if ($change > 0) {
			$delta = min($this->updateValues[$index] * RPROPConst::POSITIVE_ETA, $this->maxStep);
			$weight = ($gradients[$index] <=> 0) * $delta;
			$this->updateValues[$index] = $delta;
			$lastGradient[$index] = $gradients[$index];
		} else if ($change < 0) {
			$delta = min($this->updateValues[$index] * RPROPConst::NEGATIVE_ETA, RPROPConst::DELTA_MIN);
			$this->updateValues[$index] = $delta;
			if ($this->getError() > $this->lastError) {
				$weight = -$this->lastWeightChange[$index];
			}
			$lastGradient[$index] = 0.0;
		} else if ($change == 0) {
			$weight = ($gradients[$index] <=> 0) * $this->updateValues[$index];
			$lastGradient[$index] = $gradients[$index];
		}
		return $weight;
	}

	public function updateIWeightMinus(array $gradients, array &$lastGradient, int $index): float {
		$change = ($gradients[$index] * $lastGradient[$index]) <=> 0;
		if ($change > 0) {
			$delta = min($this->lastDelta[$index] * RPROPConst::POSITIVE_ETA, $this->maxStep);
			$lastGradient[$index] = $gradients[$index];
		} else {
			$delta = max($this->lastDelta[$index] * RPROPConst::NEGATIVE_ETA, RPROPConst::DELTA_MIN);
			$lastGradient[$index] = 0.0;
		}
		$this->lastDelta[$index] = $delta;
		return ($gradients[$index] <=> 0) * $delta;
	}

	public function updateJacobiWeight(array $gradients, array &$lastGradient, int $index): float {
		$change = ($gradients[$index] * $lastGradient[$index]) <=> 0;
		$delta  = $this->updateValues[$index];
		$weight = 0.0;

		if ($change > 0) {
			$delta = min($this->updateValues[$index] * RPROPConst::POSITIVE_ETA, $this->maxStep);
			$weight = ($gradients[$index] <=> 0) * $delta;
			$this->updateValues[$index] = $delta;
			$lastGradient[$index] = $gradients[$index];
		} else if ($change < 0) {
			$delta = max($this->updateValues[$index] * RPROPConst::NEGATIVE_ETA, RPROPConst::DELTA_MIN);
			$this->updateValues[$index] = $delta;
			$weight = -$this->lastWeightChange[$index];
			$lastGradient[$index] = 0.0;
		} else if ($change == 0) {
			$weight = ($gradients[$index] <=> 0) * $delta;
			$lastGradient[$index] = $gradients[$index];
		}
		if ($this->getError() > $this->lastError) {
			$weight = 1 / (2*self::$Q) * $delta;
			self::$Q++;
		} else {
			self::$Q = 1;
		}
		return $weight;
	}

	public function setType(RPROPType $type) {
		$this->type = $type;
	}

	public function postIteration() {
		parent::postIteration();
		$this->lastError = $this->getError();
	}

	public function getUpdateValues(): array {
		return $this->updateValues;
	}

	public function setUpdateValues(array $values) {
		$this->updateValues = $values;
	}
}
