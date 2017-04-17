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
namespace encog\test\ml\data\basic;

use InvalidArgumentException;
use encog\ml\data\basic\BasicMLData;
use encog\ml\data\basic\BasicMLDataCentroid;
use PHPUnit_Framework_TestCase as TestCase;

class BasicMLDataCentroidTest extends TestCase {
	public function testCreateCentroid() {
		$c1 = new BasicMLDataCentroid(new BasicMLData([1,2,3]));
		$c2 = clone $c1;

		$this->assertEquals($c1, $c2);
		$this->assertTrue($c1 !== $c2);
	}

	public function testAdd() {
		$this->expectException(InvalidArgumentException::class);
		(new BasicMLDataCentroid(new BasicMLData([1,2,3])))->add(1);
	}

	public function testRemove() {
		$this->expectException(InvalidArgumentException::class);
		(new BasicMLDataCentroid(new BasicMLData([1,2,3])))->remove(1);
	}

	public function testDistance() {
		$d1 = new BasicMLData([1,2,3]);
		$d2 = new BasicMLData([3,2,1]);
		$c = new BasicMLDataCentroid($d1);

		$this->assertEquals(2.8284271247461903, $c->distance($d2));
		$this->assertEquals(0.0, $c->distance($d1));

		$c->add($d2);

		$this->assertEquals($c->distance($d1), $c->distance($d2));

		$c->remove($d1);

		$this->assertEquals(2.8284271247461903, $c->distance($d1));
		$this->assertEquals(0.0, $c->distance($d2));
	}
}
