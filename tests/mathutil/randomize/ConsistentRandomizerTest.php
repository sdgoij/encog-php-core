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

use encog\mathutil\randomize\ConsistentRandomizer;
use encog\mathutil\randomize\Randomizer;
use PHPUnit\Framework\TestCase;

class ConsistentRandomizerTest extends TestCase {
	/**
	 * @param Randomizer $r1
	 * @param Randomizer $r2
	 * @param bool $same
	 *
	 * @dataProvider randomizeFloatRandomizerProvider
	 */
	public function testRandomizeFloat(Randomizer $r1, Randomizer $r2, bool $same) {
		for ($i = 0; $i < 1000; $i++) {
			if (!$same) {
				$this->assertNotSame(
					$r1->randomizeFloat(0),
					$r2->randomizeFloat(0)
				);
			} else {
				$this->assertSame(
					$r1->randomizeFloat(0),
					$r2->randomizeFloat(0)
				);
			}
			$this->assertSame(
				(new ConsistentRandomizer(-1, 1, $i))->nextDouble(),
				(new ConsistentRandomizer(-1, 1, $i))->nextDouble()
			);
		}
	}

	public function randomizeFloatRandomizerProvider() {
		return [
			[new ConsistentRandomizer(0, 1, 1), new ConsistentRandomizer(0, 1, 1), true],
			[new ConsistentRandomizer(100, 999, PHP_INT_MAX), new ConsistentRandomizer(100, 999, PHP_INT_MAX), true],
			[new ConsistentRandomizer(0, 10), new ConsistentRandomizer(0, 10), true],
			[new ConsistentRandomizer(0, 1, 0), new ConsistentRandomizer(0, 1, 1), false],
			[new ConsistentRandomizer(0, 1), new ConsistentRandomizer(0, 1, 1), false],
		];
	}
}
