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
namespace encog\test\ml\data\specific;

use encog\ml\data\MLDataError;
use encog\ml\data\specific\BiPolarMLData;
use PHPUnit\Framework\TestCase;

class BiPolarMLDataTest extends TestCase {
	public function testAdd() {
		$this->expectExceptionMessage("Add is not supported for bipolar data.");
		$this->expectException(MLDataError::class);
		(new BiPolarMLData())->add(0, 0.0);
	}

	public function testCreateCentroid() {
		$this->expectExceptionMessage("Not supported.");
		$this->expectException(MLDataError::class);
		(new BiPolarMLData())->createCentroid();
	}

	public function testCloneAndClear() {
		$data1 = new BiPolarMLData([1,2,3]);
		$data2 = $data1->clone();
		$data3 = clone $data1;

		$this->assertFalse($data1 === $data2);
		$this->assertFalse($data1 === $data3);
		$this->assertFalse($data2 === $data3);
		$data1->clear();

		$this->assertEquals([-1,-1,-1], $data1->getData()->toArray());
		$this->assertNotEquals($data1, $data2);
		$this->assertEquals($data2, $data3);
	}

	public function testGetBoolean() {
		$data = new BiPolarMLData([-1,-0.99,0,0.3,1]);
		$this->assertFalse($data->getBoolean(0));
		$this->assertFalse($data->getBoolean(1));
		$this->assertFalse($data->getBoolean(2));
		$this->assertTrue($data->getBoolean(3));
		$this->assertTrue($data->getBoolean(4));
	}

	public function testSetBoolean() {
		$data = new BiPolarMLData(5);
		$data->setBoolean(0, true);
		$data->setBoolean(1, true);
		$data->setBoolean(3, true);

		$this->assertEquals([1,1,-1,1,-1], $data->getData()->toArray());
	}

	public function testGetDataAt() {
		$data = new BiPolarMLData([-1,-0.99,0,0.3,1]);
		$this->assertEquals(-1, $data->getDataAt(0));
		$this->assertEquals(-1, $data->getDataAt(1));
		$this->assertEquals(-1, $data->getDataAt(2));
		$this->assertEquals(1, $data->getDataAt(3));
		$this->assertEquals(1, $data->getDataAt(4));
	}

	public function testSetDataAt() {
		$data = new BiPolarMLData(5);
		$data->setDataAt(0, -1);
		$data->setDataAt(1, 1);
		$data->setDataAt(3, 0.1);

		$this->assertEquals([-1,1,-1,1,-1], $data->getData()->toArray());
	}

	public function testSetData() {
		$data = new BiPolarMLData(5);
		$data->setData(\SplFixedArray::fromArray([-1,0,0.1,0.01,2]));
		$this->assertEquals(-1, $data->getDataAt(0));
		$this->assertEquals(-1, $data->getDataAt(1));
		$this->assertEquals(1, $data->getDataAt(2));
		$this->assertEquals(1, $data->getDataAt(3));
		$this->assertEquals(1, $data->getDataAt(4));
	}

	public function testSize() {
		$this->assertEquals(0, (new BiPolarMLData())->size());
		$this->assertEquals(3, (new BiPolarMLData([1,2,3]))->size());
		$this->assertEquals(9, (new BiPolarMLData(9))->size());
	}

	public function testToString() {
		$this->assertEquals("[F,F,T]", (string)new BiPolarMLData([-1,0,1]));
	}
}
