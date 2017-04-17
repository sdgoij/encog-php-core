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
namespace encog\ml\train;

use encog\ml\data\MLDataSet;
use encog\ml\train\strategy\end\EndTrainingStrategy;
use encog\ml\train\strategy\Strategy;
use encog\ml\TrainingImplementationType;

/**
 * An abstract class that implements basic training for most training
 * algorithms. Specifically training strategies can be added to enhance the
 * training.
 */
abstract class BasicTraining implements MLTrain {
	/** @var Strategy[] */
	private $strategies = [];

	/** @var MLDataSet */
	private $training;

	/** @var float */
	private $error = 0.0;

	/** @var int */
	private $iteration;

	/** @var TrainingImplementationType */
	private $implType;

	public function __construct(TrainingImplementationType $type = null) {
		$this->implType = $type ?? new TrainingImplementationType(null);
		$this->iteration = 0;
	}

	public function addStrategy(Strategy $strategy) {
		$this->strategies[] = $strategy;
		$strategy->init($this);
	}

	public function finishTraining() {
	}

	public function getError(): float {
		return $this->error;
	}

	public function getIteration(): int {
		return $this->iteration;
	}

	public function getStrategies(): array {
		return $this->strategies;
	}

	public function getTraining(): MLDataSet {
		return $this->training;
	}

	public function getTrainingImplementationType(): TrainingImplementationType {
		return $this->implType;
	}

	public function isTrainingDone(): bool {
		foreach ($this->strategies as $strategy) {
			if ($strategy instanceof EndTrainingStrategy) {
				if ($strategy->shouldStop()) {
					return true;
				}
			}
		}
		return false;
	}

	public function iteration(int $count = 1) {
		for ($i = 0; $i < $count; $i++) {
			$this->preIteration();
			$this->doIteration();
			$this->postIteration();
		}
	}

	public function preIteration() {
		foreach ($this->strategies as $strategy) {
			$strategy->preIteration();
		}
		$this->iteration++;
	}

	public function postIteration() {
		foreach ($this->strategies as $strategy) {
			$strategy->postIteration();
		}
	}

	public function setError(float $error) {
		$this->error = $error;
	}

	public function setIteration(int $iteration) {
		$this->iteration = $iteration;
	}

	public function setTraining(MLDataSet $training) {
		$this->training = $training;
	}

	abstract protected function doIteration();
}
