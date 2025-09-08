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
namespace encog\test\ml\data\temporal;

use encog\ml\data\temporal\TemporalPoint;
use PHPUnit\Framework\TestCase;

class TemporalPointTest extends TestCase {
	public function testToString() {
		$this->assertEquals("[TemporalPoint:Seq:0,Data:0,0,0]", (string)new TemporalPoint(3));
	}

	public function testCompare() {
		$t1 = new TemporalPoint(3);
		$t2 = new TemporalPoint(3);

		$this->assertEquals(0, $t1->compare($t2));

		$t2->setSequence(1);
		$this->assertEquals(-1, $t1->compare($t2));

		$t1->setSequence(2);
		$this->assertEquals(1, $t1->compare($t2));
	}

	public function testData() {
		$temporal = new TemporalPoint(3);
		$temporal->setDataAt(0, 1);
		$temporal->setDataAt(1, 2);
		$temporal->setDataAt(2, 3);

		$this->assertEquals([1,2,3], $temporal->getData()->toArray());

		$temporal->setData(\SplFixedArray::fromArray([3,2,1]));

		$this->assertEquals(3, $temporal->getDataAt(0));
		$this->assertEquals(2, $temporal->getDataAt(1));
		$this->assertEquals(1, $temporal->getDataAt(2));
	}
}
