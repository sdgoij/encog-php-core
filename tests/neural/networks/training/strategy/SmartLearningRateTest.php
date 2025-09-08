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
namespace encog\test\neural\networks\training\strategy;

use encog\ml\data\MLDataSet;
use encog\ml\train\MLTrain;
use encog\neural\networks\training\LearningRate;
use encog\neural\networks\training\strategy\SmartLearningRate;
use encog\neural\networks\training\TrainingError;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SmartLearningRateTest extends TestCase {
	public function testIncompatibleTrainer() {
		$this->expectException(TrainingError::class);
		$this->expectExceptionMessage("Trainer must implement LearningRate.");
		/** @var MLTrain|MockObject $trainer */
		$trainer = $this->createMock(MLTrain::class);
		(new SmartLearningRate())->init($trainer);
	}

	public function testSmartLearningRate() {
		/** @var MLDataSet|MockObject $training */
		$training = $this->createMock(MLDataSet::class);
		$training->expects($this->once())
			->method("getRecordCount")
			->willReturn(4);

		/** @var MLTrain|MockObject $trainer */
		$trainer = $this->getMockForAbstractClass(LearningRateTrainer::class);
		$trainer->expects($this->exactly(3))
			->method("getError")
			->willReturn(0.1, 0.2, 0.3);
		$trainer->expects($this->once())
			->method("getTraining")
			->willReturn($training);
		$trainer->expects($this->exactly(2))
			->method("setLearningRate")
			->withConsecutive(
				[0.25],
				[0.25 * SmartLearningRate::LEARNING_DECAY]
			);

		$strategy = new SmartLearningRate();
		$strategy->init($trainer);
		$strategy->preIteration();
		$strategy->postIteration();
		$strategy->preIteration();
		$strategy->postIteration();
	}
}

abstract class LearningRateTrainer implements MLTrain, LearningRate {}
