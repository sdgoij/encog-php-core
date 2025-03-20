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
namespace encog\test\mathutil;

use encog\mathutil\NumericRange;
use PHPUnit\Framework\TestCase;

class NumericRangeTest extends TestCase {
	public function testNumericRange() {
		$range = new NumericRange([1,2,3,4]);
		$this->assertEquals(0, $range->getLow());
		$this->assertEquals(4, $range->getHigh());
		$this->assertEquals(4, $range->getSamples());
		$this->assertEquals(2.5, $range->getMean());
		$this->assertEquals(2.7386127875258306, $range->getRms());
		$this->assertEquals(1.118033988749895, $range->getSd());
	}

	public function testToString() {
		$expect = "Range: 0.00000 to 4.00000,samples: 4,mean: 2.50000,rms: 2.73861,s.deviation: 1.11803";
		$this->assertEquals($expect, (string)new NumericRange([1,2,3,4]));
	}
}
