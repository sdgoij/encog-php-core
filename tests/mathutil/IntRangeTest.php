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
namespace encog\test\mathutil;

use encog\mathutil\IntRange;
use PHPUnit\Framework\TestCase;

class IntRangeTest extends TestCase {
	public function testIntRange() {
		$range = new IntRange(42, 0);
		$this->assertEquals(0, $range->getLow());
		$this->assertEquals(42, $range->getHigh());
		$range->setLow(42);
		$range->setHigh(43);
		$this->assertEquals(42, $range->getLow());
		$this->assertEquals(43, $range->getHigh());
	}
}
