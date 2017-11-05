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

use encog\ml\data\basic\BasicMLDataCentroid;
use encog\ml\data\basic\BasicMLData;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RangeException;
use SplFixedArray;

class BasicMLDataTest extends TestCase {
	public function testClone() {
		$d1 = new BasicMLData(SplFixedArray::fromArray([1, 2, 3]));
		$d2 = $d1->clone();
		$d3 = clone $d2;

		$this->assertTrue($d1 !== $d2);
		$this->assertTrue($d2 !== $d3);
		$this->assertEquals($d1, $d2);
		$this->assertEquals($d2, $d3);
	}

	public function testCopy() {
		$d1 = new BasicMLData(SplFixedArray::fromArray([1,2,3]));
		$d2 = BasicMLData::copy($d1);

		$this->assertTrue($d1 !== $d2);
		$this->assertEquals($d1, $d2);
	}

	public function testIsCountable() {
		$this->assertEquals(3, count(new BasicMLData(SplFixedArray::fromArray([1,2,3]))));
	}

	public function testToString() {
		$this->assertEquals(sprintf("[%s:1,2,3]", BasicMLData::class), new BasicMLData(SplFixedArray::fromArray([1,2,3])));
	}

	public function testAdd() {
		$data = new BasicMLData(SplFixedArray::fromArray([1,2,2]));
		$data->add(2, 1);

		$this->assertEquals([1,2,3], $data->getData()->toArray());
		$this->expectException(RangeException::class);
		$data->add(3, 1);
	}

	public function testClear() {
		$data = new BasicMLData([1,1,1]);
		$data->clear();

		$this->assertEquals([0,0,0], $data->getData()->toArray());
	}

	public function testGetData() {
		$this->assertEquals(range(0,9), (new BasicMLData(SplFixedArray::fromArray(range(0,9))))->getData()->toArray());
	}

	public function testGetDataAt() {
		$data = new BasicMLData([1,2,3]);
		$this->assertEquals(1, $data->getDataAt(0));
		$this->assertEquals(2, $data->getDataAt(1));
		$this->assertEquals(3, $data->getDataAt(2));
	}

	public function testSetData() {
		$data = new BasicMLData();
		$data->setData(SplFixedArray::fromArray([1,2,3]));

		$this->assertEquals([1,2,3], $data->getData()->toArray());
	}

	public function testSetDataAt() {
		$data = new BasicMLData([1,2,3]);
		$data->setDataAt(0, 3);
		$data->setDataAt(1, 2);
		$data->setDataAt(2, 1);

		$this->assertEquals([3,2,1], $data->getData()->toArray());
	}

	public function testSize() {
		$this->assertEquals(3, (new BasicMLData([1,2,3]))->size());
		$this->assertEquals(0, (new BasicMLData([]))->size());
	}

	public function testPlus() {
		$d1 = new BasicMLData([1,2,3]);
		$d2 = new BasicMLData([3,2,1]);

		$this->assertNotEquals($d1, $d1->plus($d2));
		$this->assertEquals(new BasicMLData([4,4,4]), $d1->plus($d2));
		$this->expectException(InvalidArgumentException::class);
		$d1->plus(new BasicMLData([1]));
	}

	public function testMinus() {
		$d1 = new BasicMLData([4,4,4]);
		$d2 = new BasicMLData([3,2,1]);

		$this->assertNotEquals($d1, $d1->minus($d2));
		$this->assertEquals(new BasicMLData([1,2,3]), $d1->minus($d2));
		$this->expectException(InvalidArgumentException::class);
		$d1->minus(new BasicMLData([1]));
	}

	public function testTimes() {
		$d1 = new BasicMLData([1,2,3]);
		$d2 = $d1->times(2);

		$this->assertEquals(new BasicMLData([2,4,6]), $d2);
		$this->assertNotEquals($d1, $d2);
	}

	public function testThreshold() {
		$d1 = new BasicMLData([1,2,3]);
		$d2 = $d1->threshold(2, -1, 1);

		$this->assertEquals(new BasicMLData([-1,-1,1]), $d2);
		$this->assertNotEquals($d1, $d2);
	}

	public function testCentroid() {
		$this->assertInstanceOf(BasicMLDataCentroid::class, (new BasicMLData(10))->createCentroid());
	}
}
