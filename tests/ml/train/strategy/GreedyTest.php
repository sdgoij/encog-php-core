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

use encog\ml\MLEncodable;
use encog\ml\MLMethod;
use encog\ml\train\MLTrain;
use encog\ml\train\strategy\Greedy;
use encog\neural\networks\training\TrainingError;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class GreedyTest extends TestCase {
	public function testInitMethodInvalid() {
		/** @var MLTrain|MockObject $trainer */
		$trainer = $this->createMock(MLTrain::class);
		$trainer->expects($this->once())->method("getMethod")->willReturn(new class implements MLMethod {});

		$this->expectException(TrainingError::class);
		$this->expectExceptionMessage("To make use of the Greedy strategy the machine learning method must support MLEncodable.");
		$strategy = new Greedy();
		$strategy->init($trainer);
	}

	public function testDropIteration() {
		/** @var MLTrain|MockObject $trainer */
		$trainer = $this->createMock(MLTrain::class);
		$trainer->expects($this->exactly(2))->method("getMethod")->willReturn(
			new class implements MLMethod, MLEncodable {
				public function encodedArrayLength(): int { return 1; }
				public function encodeToArray(array &$data) { $data = [$this->v]; }
				public function decodeFromArray(array $data) { $this->v = $data[0]; }
				public $v;
			}
		);
		$trainer->expects($this->exactly(3))->method("setError");
		$trainer->expects($this->exactly(3))->method("getError")
			->willReturn(1, 2, 3);

		$strategy = new Greedy();
		$strategy->init($trainer);
		$strategy->preIteration();
		$strategy->postIteration();
		$strategy->preIteration();
		$strategy->postIteration();
	}
}
