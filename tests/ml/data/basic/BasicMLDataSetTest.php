<?php
/**
 * Copyright 2015-2016 Tim Jurcka <sdgoij@gmail.com>
 *
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
use encog\ml\data\basic\BasicMLDataSet;
use PHPUnit_Framework_TestCase as TestCase;
use RangeException;

class BasicMLDataSetTest extends TestCase {
	public function testCreateDataSet() {
		$expect = [new BasicMLDataPair(new BasicMLData([1,2,3]), new BasicMLData([3,2,1]))];
		$data1 = new BasicMLDataSet([$expect[0]->getInputArray()], [$expect[0]->getIdealArray()]);
		$data2 = new BasicMLDataSet([$expect[0]->getInputArray()], [$expect[0]->getIdeal()]);
		$data3 = new BasicMLDataSet([$expect[0]->getInput()], [$expect[0]->getIdealArray()]);
		$data4 = new BasicMLDataSet([$expect[0]->getInput()], [$expect[0]->getIdeal()]);
		$data5 = new BasicMLDataSet($expect);

		$this->assertEquals($expect, $data1->getData());
		$this->assertEquals($expect, $data2->getData());
		$this->assertEquals($data1, $data2);
		$this->assertEquals($data2, $data3);
		$this->assertEquals($data3, $data4);
		$this->assertEquals($data4, $data5);
	}

	public function testAdd() {
		$data = new BasicMLDataSet([]);
		$this->assertEquals(false, $data->isSupervised());
		$this->assertEquals(0, $data->getRecordCount());
		$this->assertEquals(0, $data->getInputSize());
		$this->assertEquals(0, $data->getIdealSize());

		$data->add(new BasicMLData([1,2]));
		$data->add(new BasicMLData([3,4]));
		$data->add(new BasicMLData([5,6]));

		$this->assertEquals(false, $data->isSupervised());
		$this->assertEquals(3, $data->getRecordCount());
		$this->assertEquals(2, $data->getInputSize());
		$this->assertEquals(0, $data->getIdealSize());

		$data = new BasicMLDataSet([]);
		$data->add(new BasicMLData([1,2]), new BasicMLData([1]));
		$data->add(new BasicMLData([3,4]), new BasicMLData([2]));
		$data->add(new BasicMLData([5,6]), new BasicMLData([3]));

		$this->assertEquals(true, $data->isSupervised());
		$this->assertEquals(3, $data->getRecordCount());
		$this->assertEquals(2, $data->getInputSize());
		$this->assertEquals(1, $data->getIdealSize());
	}

	public function testAddPair() {
		$data = new BasicMLDataSet([]);
		$data->addPair(new BasicMLDataPair(new BasicMLData([1,2])));
		$data->addPair(new BasicMLDataPair(new BasicMLData([3,4])));
		$data->addPair(new BasicMLDataPair(new BasicMLData([5,6])));

		$this->assertEquals(false, $data->isSupervised());
		$this->assertEquals(3, $data->getRecordCount());
		$this->assertEquals(2, $data->getInputSize());
		$this->assertEquals(0, $data->getIdealSize());

		$data = new BasicMLDataSet([]);
		$data->addPair(new BasicMLDataPair(new BasicMLData([1,2]), new BasicMLData([1])));
		$data->addPair(new BasicMLDataPair(new BasicMLData([3,4]), new BasicMLData([2])));
		$data->addPair(new BasicMLDataPair(new BasicMLData([5,6]), new BasicMLData([3])));

		$this->assertEquals(true, $data->isSupervised());
		$this->assertEquals(3, $data->getRecordCount());
		$this->assertEquals(2, $data->getInputSize());
		$this->assertEquals(1, $data->getIdealSize());
	}

	public function testGetIdealSize() {
		$data1 = new BasicMLDataSet([new BasicMLDataPair(new BasicMLData([1,2,3]), new BasicMLData([3,2,1]))]);
		$data2 = new BasicMLDataSet([new BasicMLDataPair(new BasicMLData([1,2,3]), null)]);
		$this->assertEquals(3, $data1->getIdealSize());
		$this->assertEquals(0, $data2->getIdealSize());
	}

	public function testGetInputSize() {
		$data1 = new BasicMLDataSet([new BasicMLDataPair(new BasicMLData([1,2,3]), null)]);
		$data2 = new BasicMLDataSet([]);
		$this->assertEquals(3, $data1->getInputSize());
		$this->assertEquals(0, $data2->getInputSize());
	}

	public function testIsSupervised() {
		$this->assertFalse((new BasicMLDataSet([BasicMLDataPair::createPair(1,0)]))->isSupervised());
		$this->assertTrue((new BasicMLDataSet([BasicMLDataPair::createPair(1,1)]))->isSupervised());
	}

	public function testGetRecordCount() {
		$data = new BasicMLDataSet([BasicMLDataPair::createPair(2,3), BasicMLDataPair::createPair(2,3)]);
		$this->assertEquals(0, (new BasicMLDataSet([]))->getRecordCount());
		$this->assertEquals(2, $data->getRecordCount());
	}

	public function testGetRecord() {
		$expect = new BasicMLDataPair(new BasicMLData([1]), new BasicMLData([2]));
		$pair = BasicMLDataPair::createPair(1,1);
		$data = new BasicMLDataSet([[1]], [[2]]);
		$data->getRecord(0, $pair);

		$this->assertEquals($expect, $pair);
		$this->setExpectedException(RangeException::class);
		$data->getRecord(1, $pair);
	}

	public function testGet() {
		$expect = new BasicMLDataPair(new BasicMLData([1]), new BasicMLData([2]));
		$data = new BasicMLDataSet([[1]], [[2]]);

		$this->assertEquals($expect, $data->get(0));
		$this->setExpectedException(RangeException::class);
		$data->get(1);
	}

	public function testIterator() {
		$expect[] = BasicMLDataPair::createPair(2,3);
		$expect[] = BasicMLDataPair::createPair(2,3);
		$expect[] = BasicMLDataPair::createPair(2,3);
		foreach (new BasicMLDataSet($expect) as $key => $pair) {
			$this->assertEquals($expect[$key], $pair);
		}
	}

	public function testSize() {
		$this->assertEquals(0, (new BasicMLDataSet([]))->size());
		$this->assertEquals(1, (new BasicMLDataSet([[1]]))->size());
		$this->assertEquals(2, (new BasicMLDataSet([[1], [1]]))->size());
	}
}
