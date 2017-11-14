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
namespace encog\neural\som\training\clustercopy;

use encog\ml\data\MLData;
use encog\ml\data\MLDataPair;
use encog\ml\data\MLDataSet;
use encog\ml\MLMethod;
use encog\ml\train\BasicTraining;
use encog\ml\TrainingImplementationType;
use encog\neural\networks\training\propagation\TrainingContinuation;
use encog\neural\NeuralNetworkError;
use encog\neural\som\SOM;

/**
 * SOM cluster copy is a very simple trainer for SOM's. Using this trainer all of
 * the training data is copied to the SOM weights. This can provide a functional
 * SOM, or can be used as a starting point for training.
 *
 * For now, this trainer will only work if you have equal or fewer training elements
 * to the number of output neurons.
 */
class SOMClusterCopyTraining extends BasicTraining {
	public function __construct(SOM $som, MLDataSet $training) {
		parent::__construct(new TrainingImplementationType(TrainingImplementationType::OnePass));
		if ($som->getOutputCount() < $training->getRecordCount()) {
			throw new NeuralNetworkError(
				"To use cluster copy training you must have at least as many output neurons as training elements.");
		}
		$this->setTraining($training);
		$this->som = $som;
	}

	public function canContinue(): bool {
		return false;
	}

	public function pause(): TrainingContinuation {
		throw new NeuralNetworkError("Training cannot be paused.");
	}

	public function resume(TrainingContinuation $state) {}

	public function getMethod(): MLMethod {
		return $this->som;
	}

	public function isTrainingDone(): bool {
		return parent::isTrainingDone() || $this->done;
	}

	protected function doIteration() {
		$output = 0;
		/** @var MLDataPair $pair */
		foreach ($this->getTraining() as $pair) {
			$this->copyInputPattern($output++, $pair->getInput());
		}
		$this->done = true;
	}

	private function copyInputPattern(int $output, MLData $input) {
		for ($i = 0; $i < $this->som->getInputCount(); $i++) {
			$this->som->getWeights()->setRowCol($output, $i, $input->getDataAt($i));
		}
	}

	/** @var bool */
	private $done = false;

	/** @var SOM */
	private $som;
}
