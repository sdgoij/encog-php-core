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

use encog\ml\data\basic\BasicMLData;
use encog\ml\data\basic\BasicMLDataPair;
use encog\ml\data\basic\BasicMLDataPairCentroid;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class BasicMLDataPairCentroidTest extends TestCase {
	public function testCreateCentroid() {
		$c1 = new BasicMLDataPairCentroid(new BasicMLDataPair(new BasicMLData([1,2,3])));
		$c2 = clone $c1;

		$this->assertEquals($c1, $c2);
		$this->assertTrue($c1 !== $c2);
	}

	public function testAdd() {
		$this->expectException(InvalidArgumentException::class);
		(new BasicMLDataPairCentroid(new BasicMLDataPair(new BasicMLData([1,2,3]))))->add(1);
	}

	public function testRemove() {
		$this->expectException(InvalidArgumentException::class);
		(new BasicMLDataPairCentroid(new BasicMLDataPair(new BasicMLData([1,2,3]))))->remove(1);
	}

	public function testDistance() {
		$p[] = new BasicMLDataPair(new BasicMLData([1,2,3]));
		$p[] = new BasicMLDataPair(new BasicMLData([3,2,1]));
		$c = new BasicMLDataPairCentroid($p[0]);

		$this->assertEquals(2.8284271247461903, $c->distance($p[1]));
		$this->assertEquals(0.0, $c->distance($p[0]));

		$c->add($p[1]);

		$this->assertEquals(2.1213203435596424, $c->distance($p[1]));
		$this->assertEquals(0.7071067811865476, $c->distance($p[0]));

		$c->remove($p[0]);

		$this->assertEquals(1.7677669529663689, $c->distance($p[1]));
		$this->assertEquals(1.0606601717798212, $c->distance($p[0]));
		$this->expectException(InvalidArgumentException::class);
		$c->distance(1);
	}
}
