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
namespace encog\test\ml\train\strategy;

use encog\ml\train\MLTrain;
use encog\ml\train\strategy\StopTraining;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class StopTrainingTest extends TestCase {
	public function testShouldStop() {
		$strategy = new StopTraining(StopTraining::DEFAULT_MIN_IMPROVEMENT, 0);
		/** @var MLTrain|MockObject $trainer */
		$trainer = $this->createMock(MLTrain::class);
		$trainer->expects($this->exactly(3))
			->method("getError")
			->willReturn(0.1);
		$strategy->init($trainer);
		$strategy->postIteration();

		$this->assertFalse($strategy->shouldStop());
		$strategy->postIteration();

		$this->assertTrue($strategy->shouldStop());
	}
}
