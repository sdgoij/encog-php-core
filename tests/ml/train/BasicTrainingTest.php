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
use encog\ml\train\strategy\EndTraining;
use encog\ml\train\strategy\Strategy;
use encog\ml\TrainingImplementationType;
use encog\neural\networks\training\propagation\TrainingContinuation;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BasicTrainingTest extends TestCase {
	public function testTrainingImplementationType() {
		$Background = new TrainingImplementationType(TrainingImplementationType::Background);
		$Iterative = new TrainingImplementationType(TrainingImplementationType::Iterative);
		$OnePass = new TrainingImplementationType(TrainingImplementationType::OnePass);
		$Null = new TrainingImplementationType(null);

		/** @var BasicTraining[]|MockObject[] $trainers[] */
		$trainers[] = $this->getMockForAbstractClass(BasicTraining::class, [$Background]);
		$trainers[] = $this->getMockForAbstractClass(BasicTraining::class, [$Iterative]);
		$trainers[] = $this->getMockForAbstractClass(BasicTraining::class, [$OnePass]);
		$trainers[] = $this->getMockForAbstractClass(BasicTraining::class, [$Null]);

		$this->assertEquals($Background, $trainers[0]->getTrainingImplementationType());
		$this->assertEquals($Iterative, $trainers[1]->getTrainingImplementationType());
		$this->assertEquals($OnePass, $trainers[2]->getTrainingImplementationType());
		$this->assertEquals($Null, $trainers[3]->getTrainingImplementationType());
	}

	public function testAddStrategy() {
		/** @var Strategy[]|MockObject[] $strategies */
		$strategies[] = $this->createMock(Strategy::class);
		$strategies[] = $this->createMock(Strategy::class);
		$strategies[] = $this->createMock(Strategy::class);

		/** @var BasicTraining|MockObject $trainer */
		$trainer = $this->getMockForAbstractClass(BasicTraining::class);

		$strategies[0]->expects($this->once())
			->method("init")
			->with($trainer);
		$strategies[1]->expects($this->once())
			->method("init")
			->with($trainer);
		$strategies[2]->expects($this->once())
			->method("init")
			->with($trainer);

		$trainer->addStrategy($strategies[0]);
		$trainer->addStrategy($strategies[1]);
		$trainer->addStrategy($strategies[2]);

		$this->assertCount(3, $trainer->getStrategies());
	}

	public function testIsTrainingDone() {
		/** @var Strategy[]|MockObject[] $strategies */
		$strategies[] = $this->createMock(Strategy::class);
		$strategies[] = $this->createMock(EndTraining::class);
		$strategies[] = $this->createMock(EndTraining::class);
		$strategies[] = $this->createMock(EndTraining::class);

		/** @var BasicTraining|MockObject $trainer */
		$trainer = $this->getMockForAbstractClass(BasicTraining::class);
		$trainer->addStrategy($strategies[0]);
		$trainer->addStrategy($strategies[1]);
		$trainer->addStrategy($strategies[2]);
		$trainer->addStrategy($strategies[3]);

		$strategies[1]->expects($this->once())
			->method("shouldStop")
			->willReturn(false);
		$strategies[2]->expects($this->once())
			->method("shouldStop")
			->willReturn(true);
		$strategies[3]->expects($this->never())
			->method("shouldStop");

		$this->assertTrue($trainer->isTrainingDone());
	}

	public function testIteration() {
		/** @var BasicTraining|MockObject $trainer */
		$trainer = $this->getMockForAbstractClass(BasicTraining::class);
		$trainer->expects($this->exactly(6))
			->method("doIteration");

		/** @var Strategy|MockObject $strategy */
		$strategy = $this->createMock(Strategy::class);
		$strategy->expects($this->exactly(6))
			->method("preIteration");
		$strategy->expects($this->exactly(6))
			->method("postIteration");

		$trainer->addStrategy($strategy);
		$trainer->iteration();

		$this->assertEquals(1, $trainer->getIteration());

		$trainer->iteration(4);
		$this->assertEquals(5, $trainer->getIteration());

		$trainer->setIteration(42);
		$this->assertEquals(42, $trainer->getIteration());

		$trainer->iteration();
		$this->assertEquals(43, $trainer->getIteration());
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
