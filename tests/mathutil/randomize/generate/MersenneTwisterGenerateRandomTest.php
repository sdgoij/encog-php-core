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
namespace encog\test\mathutil\randomize\generate;

use encog\mathutil\randomize\generate\GenerateRandom;
use encog\mathutil\randomize\generate\MersenneTwisterGenerateRandom;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class MersenneTwisterGenerateRandomTest extends TestCase {
	public function testCreateMersenneTwisterGenerateRandomNumberGenerator() {
		$g1 = new MersenneTwisterGenerateRandom(0);
		$g2 = new MersenneTwisterGenerateRandom();

		$this->assertNotSame($g1->nextLong(), $g2->nextLong());
	}

	public function testSetSeed() {
		$this->assertSameSequence(
			new MersenneTwisterGenerateRandom(1),
			new MersenneTwisterGenerateRandom(1)
		);
		$this->assertSameSequence(
			new MersenneTwisterGenerateRandom(11),
			new MersenneTwisterGenerateRandom(11)
		);
		$this->assertSameSequence(
			new MersenneTwisterGenerateRandom(111),
			new MersenneTwisterGenerateRandom(111)
		);
		$this->assertSameSequence(
			new MersenneTwisterGenerateRandom(0),
			new MersenneTwisterGenerateRandom(0)
		);
	}

	public function testSetSeedArray() {
		$this->assertSameSequence(
			new MersenneTwisterGenerateRandom([1,2,3]),
			new MersenneTwisterGenerateRandom([1,2,3])
		);

		$this->assertSameSequence(
			new MersenneTwisterGenerateRandom(range(1, MersenneTwisterGenerateRandom::N-1)),
			new MersenneTwisterGenerateRandom(range(1, MersenneTwisterGenerateRandom::N-1))
		);

		$this->assertSameSequence(
			new MersenneTwisterGenerateRandom(range(1, MersenneTwisterGenerateRandom::N+1)),
			new MersenneTwisterGenerateRandom(range(1, MersenneTwisterGenerateRandom::N+1))
		);
	}

	public function testInvalidSeedType() {
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage("Invalid seed type.");
		(new MersenneTwisterGenerateRandom("hello"));
	}

	public function assertSameSequence(GenerateRandom $g1, GenerateRandom $g2) {
		$this->assertSame($g1->nextLong(), $g2->nextLong());
		$this->assertSame($g1->nextInt(), $g2->nextInt());
		$this->assertSame($g1->nextInt(3), $g2->nextInt(3));
		$this->assertSame($g1->nextInt(30,3), $g2->nextInt(30,3));
		$this->assertSame($g1->nextFloat(), $g2->nextFloat());
		$this->assertSame($g1->nextDouble(), $g2->nextDouble());
		$this->assertSame($g1->nextDouble(0.3), $g2->nextDouble(0.3));
		$this->assertSame($g1->nextDouble(0.3, 0.003), $g2->nextDouble(0.3, 0.003));
		$this->assertSame($g1->nextBoolean(), $g2->nextBoolean());
	}
}
