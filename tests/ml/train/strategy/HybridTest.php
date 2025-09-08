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
namespace encog\test\ml\train\strategy;

use encog\ml\train\MLTrain;
use encog\ml\train\strategy\Hybrid;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class HybridTest extends TestCase {
	public function testSecondaryIteration() {
		/** @var MLTrain|MockObject $secondary */
		$secondary = $this->createMock(MLTrain::class);
		$secondary->expects($this->exactly(Hybrid::DEFAULT_ALTERNATE_CYCLES))
			->method("iteration");

		/** @var MLTrain|MockObject $trainer */
		$trainer = $this->createMock(MLTrain::class);
		$trainer->expects($this->exactly(12))
			->method("getError")
			->willReturn(1.0, ...range(2.0, 12.0));

		$strategy = new Hybrid($secondary);
		$strategy->init($trainer);
		$strategy->preIteration();
		$strategy->postIteration();

		$strategy->postIteration();
		$strategy->postIteration();
		$strategy->postIteration();
		$strategy->postIteration();
		$strategy->postIteration();
		$strategy->postIteration();
		$strategy->postIteration();
		$strategy->postIteration();
		$strategy->postIteration();
		$strategy->postIteration();

		$strategy->postIteration();
	}
}
