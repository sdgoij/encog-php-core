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
namespace encog\test\mathutil\randomize;

use encog\mathutil\matrices\Matrix;
use encog\mathutil\randomize\BasicRandomizer;
use encog\mathutil\randomize\generate\AbstractBoxMuller;
use encog\ml\MLEncodable;
use encog\neural\networks\BasicNetwork;
use encog\util\simple\EncogUtility;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BasicRandomizerTest extends TestCase {
	public function testGetSetGenerateRandom() {
		/** @var BasicRandomizer|MockObject $randomizer */
		$randomizer = $this->getMockForAbstractClass(BasicRandomizer::class);
		$randomizer->setRandom(
			new class extends AbstractBoxMuller {
				protected function next(): float {
					return 1.0;
				}
			}
		);
		$this->assertSame(1.0, $randomizer->getRandom()->nextDouble());
		$this->assertSame(1.0, $randomizer->nextDouble());
	}

	public function testRandomizeMLMethod() {
		/** @var BasicRandomizer|MockObject $randomizer */
		$randomizer = $this->getMockForAbstractClass(BasicRandomizer::class);
		{
			/** @var BasicNetwork */
			$network = EncogUtility::simpleFeedForward(2, 3, 3, 1, false);
			$sorted = range(0, $network->encodedArrayLength() - 1);
			$randomized = [];

			$network->decodeFromArray($sorted);
			$randomizer->randomize($network);
			$network->encodeToArray($randomized);

			$this->assertNotEquals($sorted, $randomized);
		}
		{
			$encodable = new class implements MLEncodable {
				public function encodedArrayLength(): int { return 1; }
				public function encodeToArray(array &$data) { $data = [$this->v]; }
				public function decodeFromArray(array $data) { $this->v = $data[0]; }
				public $v = 0.0;
			};
			$randomizer->method("randomizeFloat")->willReturn(M_PI);
			$randomizer->randomize($encodable);

			$this->assertSame(M_PI, $encodable->v);
		}
	}

	public function testRandomizeArray() {
		/** @var BasicRandomizer|MockObject $randomizer */
		$randomizer = $this->getMockForAbstractClass(BasicRandomizer::class);
		$sorted = range(1, 1024);
		$randomized = $sorted;

		$randomizer->randomizeArray($randomized);
		$this->assertNotEquals($sorted, $randomized);
	}

	public function testRandomizeArray2D() {
		/** @var BasicRandomizer|MockObject $randomizer */
		$randomizer = $this->getMockForAbstractClass(BasicRandomizer::class);
		$sorted = range(1, 48);
		$bucket = array_map(function () use ($sorted) { return $sorted; }, $sorted);
		$randomized = $bucket;

		$randomizer->randomizeArray2D($randomized);
		$this->assertNotEquals($bucket, $randomized);
	}

	public function testRandomizeMatrix() {
		/** @var BasicRandomizer|MockObject $randomizer */
		$randomizer = $this->getMockForAbstractClass(BasicRandomizer::class);
		$matrix = Matrix::createZero(96, 96);
		$sequence = array_map(
			function (int $value): float { return $value / 10; },
			range(1, $matrix->getRows() * $matrix->getCols())
		);
		$first = array_shift($sequence);

		$randomizer->expects($this->exactly(count($sequence)+1))
			->method("randomizeFloat")
			->willReturn($first, ...$sequence);

		$randomizer->randomizeMatrix($matrix);
		array_unshift($sequence, $first);

		for ($i = 0; $i < $matrix->getRows(); $i++) {
			$this->assertEquals(
				array_slice($sequence, $i*$matrix->getCols(), $matrix->getCols()),
				$matrix->getRow($i)->getData()[0]
			);
		}
	}
}
