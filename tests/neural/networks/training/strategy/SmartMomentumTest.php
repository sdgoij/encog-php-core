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

use encog\ml\train\MLTrain;
use encog\ml\train\strategy\Strategy;
use encog\neural\networks\training\Momentum;
use encog\neural\networks\training\strategy\SmartMomentum;
use encog\neural\networks\training\TrainingError;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SmartMomentumTest extends TestCase {
	public function testIncompatibleTrainer() {
		$this->expectException(TrainingError::class);
		$this->expectExceptionMessage("Trainer must implement Momentum.");
		/** @var MLTrain|MockObject $trainer */
		$trainer = $this->createMock(MLTrain::class);
		(new SmartMomentum())->init($trainer);
	}

	public function testSmartMomentum() {
		/** @var MLTrain|MockObject $trainer */
		$trainer = $this->getMockForAbstractClass(MomentumTrainer::class);
		$trainer->expects($this->exactly(27))
			->method("getError")
			->willReturn(
				0.2,
				0.2, 0.1, // (0.1-0.2)/0.2 = -0.5 => triggers setMomentum(0.0)

				0.1, 0.1,
				0.1, 0.1,
				0.1, 0.1,
				0.1, 0.1,
				0.1, 0.1,
				0.1, 0.1,
				0.1, 0.1,
				0.1, 0.1,
				0.1, 0.1,
				0.1, 0.1,
				0.1, 0.1,

				0.2, 0.1  // again, triggers setMomentum(0.0)
			);
		$trainer->expects($this->exactly(4))
			->method("setMomentum")
			->withConsecutive(
				[0.0],
				[0.0],
				[SmartMomentum::START_MOMENTUM*(1.0+SmartMomentum::MOMENTUM_INCREASE)],
				[0.0]
			);

		$strategy = new SmartMomentum();
		$strategy->init($trainer);

		$this->iter($strategy, 2+SmartMomentum::MOMENTUM_CYCLES+1+1);
	}

	private function iter(Strategy $strategy, int $n = 1) {
		do {
			$strategy->preIteration();
			$strategy->postIteration();
		} while (--$n > 0);
	}
}

abstract class MomentumTrainer implements MLTrain, Momentum {}
