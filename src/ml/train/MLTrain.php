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
namespace encog\ml\train;

use encog\ml\data\MLDataSet;
use encog\ml\MLMethod;
use encog\ml\train\strategy\Strategy;
use encog\ml\TrainingImplementationType;
use encog\neural\networks\training\propagation\TrainingContinuation;

/**
 * Defines a training method for a machine learning method. Most MLMethod
 * objects need to be trained in some way before they are ready for use.
 */
interface MLTrain {
	public function getTrainingImplementationType(): TrainingImplementationType;
	public function isTrainingDone(): bool;
	public function getTraining(): MLDataSet;
	public function iteration(int $count = 1);
	public function getError(): float;
	public function finishTraining();
	public function getIteration(): int;
	public function canContinue(): bool;
	public function pause(): TrainingContinuation;
	public function resume(TrainingContinuation $state);
	public function addStrategy(Strategy $strategy);
	public function getMethod(): MLMethod;
	public function getStrategies(): array;
	public function setError(float $error);
	public function setIteration(int $iteration);
}
