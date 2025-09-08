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

use encog\mathutil\randomize\RangeRandomizer;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class RangeRandomizerTest extends TestCase {
	public function testRandomInt() {
		$this->assertInRange(0, 1, RangeRandomizer::randomInt(0, 1));
		$this->assertInRange(0, 9, RangeRandomizer::randomInt(0, 9));
		$this->assertInRange(1, 3, RangeRandomizer::randomInt(1, 3));
	}

	/**
	 * @param RangeRandomizer $r
	 * @param $min
	 * @param $max
	 *
	 * @dataProvider randomizeFloatProvider
	 */
	public function testRandomizeFloat(RangeRandomizer $r, $min, $max) {
		$this->assertInRange($min, $max, $r->randomizeFloat(0));
		$this->assertInRange($min, $max, $r->randomizeFloat(0));
		$this->assertInRange($min, $max, $r->randomizeFloat(0));
		$this->assertInRange($min, $max, $r->randomizeFloat(0));
		$this->assertInRange($min, $max, $r->randomizeFloat(0));
		$this->assertInRange($min, $max, $r->randomizeFloat(0));
	}

	public function testMinMaxRange() {
		$randomizer = new RangeRandomizer(0, 9);
		$this->assertSame(0.0, $randomizer->getMin());
		$this->assertSame(9.0, $randomizer->getMax());
	}

	public function randomizeFloatProvider() {
		return [
			[new RangeRandomizer(0, 1), 0, 1],
			[new RangeRandomizer(0.1, 0.9), 0.1, 0.9],
			[new RangeRandomizer(0.01, 0.02), 0.01, 0.02],
			[new RangeRandomizer(1, 100), 1, 100],
		];
	}

	private function assertInRange($min, $max, $v) {
		$this->assertThat($v, Assert::logicalAnd(
			Assert::greaterThanOrEqual($min),
			Assert::lessThanOrEqual($max)
		));
	}
}
