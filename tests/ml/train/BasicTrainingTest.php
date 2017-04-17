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
namespace encog\test\ml\train;

use encog\ml\MLMethod;
use encog\ml\train\BasicTraining;
use encog\ml\TrainingImplementationType;
use encog\neural\networks\training\propagation\TrainingContinuation;
use PHPUnit_Framework_TestCase as TestCase;

class BasicTrainingTest extends TestCase {
	public function testTrainingImplementationType() {
		$Background = new TrainingImplementationType(TrainingImplementationType::Background);
		$Iterative = new TrainingImplementationType(TrainingImplementationType::Iterative);
		$OnePass = new TrainingImplementationType(TrainingImplementationType::OnePass);
		$Null = new TrainingImplementationType(null);

		$this->assertEquals($Background, (new DummyTraining($Background))->getTrainingImplementationType());
		$this->assertEquals($Iterative, (new DummyTraining($Iterative))->getTrainingImplementationType());
		$this->assertEquals($OnePass, (new DummyTraining($OnePass))->getTrainingImplementationType());
		$this->assertEquals($Null, (new DummyTraining())->getTrainingImplementationType());
	}

	public function testIteration() {
		$t = new DummyTraining();
		$this->assertEquals(0, $t->getIteration());

		$t->iteration();
		$this->assertEquals(1, $t->getIteration());

		$t->iteration(2);
		$this->assertEquals(3, $t->getIteration());
	}
}

class DummyTraining extends BasicTraining {
	protected function doIteration() {}
	public function canContinue(): bool { return $this->canContinue; }
	public function pause(): TrainingContinuation { return null; }
	public function resume(TrainingContinuation $state) {}
	public function getMethod(): MLMethod { return null; }
	public $canContinue = true;
}
