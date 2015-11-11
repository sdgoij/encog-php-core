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
namespace encog\test\ml\data\versatile\division;

use encog\ml\data\versatile\division\DataDivision;
use encog\ml\data\versatile\MatrixMLDataSet;
use PHPUnit_Framework_TestCase as TestCase;

class DataDivisionTest extends TestCase {
	public function testDataDivision() {
		$dataset = new MatrixMLDataSet();
		$division = new DataDivision(10);
		$this->assertEquals(10.0, $division->getPercent());
		$this->assertEquals(0, $division->getCount());
		$this->assertEquals([], $division->getMask());
		$this->assertNull($division->getDataSet());

		$division->setDataSet($dataset);
		$division->setPercent(0.9);
		$division->setCount(42);
		$division->setMask([0,1,3]);
		$division->setMaskIndex(3, 5);

		$this->assertEquals($dataset, $division->getDataSet());
		$this->assertEquals(0.9, $division->getPercent());
		$this->assertEquals(42, $division->getCount());
		$this->assertEquals([0,1,3,5], $division->getMask());
	}
}
