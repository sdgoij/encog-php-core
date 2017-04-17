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
use encog\mathutil\ComplexNumber;
use encog\ml\data\basic\BasicMLComplexData;
use encog\ml\data\basic\BasicMLData;
use PHPUnit_Framework_TestCase as TestCase;
use RangeException;
use SplFixedArray;

class BasicMLComplexDataTest extends TestCase {
	public function testCreateFromMLData() {
		$data = BasicMLComplexData::createFromMLData(new BasicMLData([1,2,3]));
		$this->assertEquals(new BasicMLComplexData([1,2,3]), $data);
		$this->assertEquals(1, $data->getComplexDataAt(0)->getReal());
		$this->assertEquals(0, $data->getComplexDataAt(0)->getImaginary());
		$this->assertEquals(2, $data->getComplexDataAt(1)->getReal());
		$this->assertEquals(0, $data->getComplexDataAt(1)->getImaginary());
		$this->assertEquals(3, $data->getComplexDataAt(2)->getReal());
		$this->assertEquals(0, $data->getComplexDataAt(2)->getImaginary());
	}

	public function testAddComplex() {
		$data = new BasicMLComplexData([new ComplexNumber(1,2), new ComplexNumber(3,4)]);
		$data->addComplex(0, $data->getComplexDataAt(1));

		$this->assertEquals(new BasicMLComplexData([new ComplexNumber(4,6), new ComplexNumber(3,4)]), $data);
		$this->setExpectedException(RangeException::class);
		$data->addComplex(2, $data->getComplexDataAt(0));
	}

	public function testGetComplexData() {
		$data = new BasicMLComplexData([new ComplexNumber(1,2), new ComplexNumber(3,4), 5, 6]);
		foreach ($data->getComplexData() as $complex) {
			$this->assertInstanceOf(ComplexNumber::class, $complex);
		}
	}

	public function testGetComplexDataAt() {
		$data = new BasicMLComplexData([new ComplexNumber(1,2), new ComplexNumber(3,4), 5, 6]);
		$this->assertEquals(new ComplexNumber(1,2), $data->getComplexDataAt(0));
		$this->assertEquals(new ComplexNumber(3,4), $data->getComplexDataAt(1));
		$this->assertEquals(new ComplexNumber(5,0), $data->getComplexDataAt(2));
		$this->assertEquals(new ComplexNumber(6,0), $data->getComplexDataAt(3));
		$this->setExpectedException(RangeException::class);
		$data->getComplexDataAt(4);
	}

	public function testSetComplexData() {
		$data = new BasicMLComplexData(2);
		$data->setComplexData(SplFixedArray::fromArray([new ComplexNumber(1,2), new ComplexNumber(3,4)]));
		$this->assertEquals(new ComplexNumber(1,2), $data->getComplexDataAt(0));
		$this->assertEquals(new ComplexNumber(3,4), $data->getComplexDataAt(1));
		$this->setExpectedException(InvalidArgumentException::class);
		$data->setComplexData(SplFixedArray::fromArray([1,2,3]));
	}

	public function testSetComplexDataAt() {
		$expect = new ComplexNumber(1,2);
		$data = new BasicMLComplexData(1);
		$data->setComplexDataAt(0, $expect);

		$this->assertEquals($expect, $data->getComplexDataAt(0));
		$this->assertEquals(1, $data->size());
	}

	public function testAdd() {
		$expect = new BasicMLComplexData([new ComplexNumber(2,2)]);
		$data = new BasicMLComplexData([new ComplexNumber(1,2)]);
		$data->add(0, 1);

		$this->assertEquals($expect, $data);
	}

	public function testClear() {
		$expect = new BasicMLComplexData([0,0,0]);
		$data = new BasicMLComplexData([1,2,3]);
		$data->clear();

		$this->assertEquals($expect, $data);
		foreach ($data->getComplexData() as $complex) {
			$this->assertEquals(0, $complex->getImaginary());
			$this->assertEquals(0, $complex->getReal());
		}
	}

	public function testClone() {
		$d1 = new BasicMLComplexData([1,2,3]);
		$d2 = clone $d1;

		$this->assertEquals($d1, $d2);
		$this->assertTrue($d1 !== $d2);
	}

	public function testGetData() {
		$data = (new BasicMLComplexData([new ComplexNumber(1,2),new ComplexNumber(3,4), 5, 6]))->getData();
		$this->assertEquals(1, $data[0]);
		$this->assertEquals(3, $data[1]);
		$this->assertEquals(5, $data[2]);
		$this->assertEquals(6, $data[3]);
	}

	public function testGetDataAt() {
		$data = new BasicMLComplexData([new ComplexNumber(1,2),new ComplexNumber(3,4), 5, 6]);
		$this->assertEquals(1, $data->getDataAt(0));
		$this->assertEquals(3, $data->getDataAt(1));
		$this->assertEquals(5, $data->getDataAt(2));
		$this->assertEquals(6, $data->getDataAt(3));
	}
}
