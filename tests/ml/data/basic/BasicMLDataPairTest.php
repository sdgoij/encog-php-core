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
use PHPUnit_Framework_TestCase as TestCase;

class BasicMLDataPairTest extends TestCase {
	public function testCreatePair() {
		$pair = new BasicMLDataPair(new BasicMLData([0,0]),new BasicMLData([0,0]));
		$this->assertEquals($pair, BasicMLDataPair::createPair(2,2));
	}

	public function testToString() {
		$expect = sprintf("[%s:Input:[%s:0,0],Ideal:[%s:0],Significance:%f]",
			BasicMLDataPair::class,
			BasicMLData::class,
			BasicMLData::class,
			1.0);
		$this->assertEquals($expect, (string)BasicMLDataPair::createPair(2,1));
	}

	public function testGetIdealArray() {
		$this->assertEquals([0,0], BasicMLDataPair::createPair(2,2)->getIdealArray());
		$this->assertEquals([], BasicMLDataPair::createPair(1,0)->getIdealArray());
	}

	public function testGetInputArray() {
		$this->assertEquals([0,0], BasicMLDataPair::createPair(2,2)->getInputArray());
		$this->assertEquals([0], BasicMLDataPair::createPair(1,0)->getInputArray());
	}

	public function testSetIdealArray() {
		$pair = BasicMLDataPair::createPair(2,0);
		$this->assertEquals(new BasicMLData([]), $pair->getIdeal());

		$pair->setIdealArray([1,2]);
		$this->assertEquals(new BasicMLData([1,2]), $pair->getIdeal());

		$pair->setIdealArray([2,1]);
		$this->assertEquals(new BasicMLData([2,1]), $pair->getIdeal());
	}

	public function testSetInputArray() {
		$pair = BasicMLDataPair::createPair(2,0);
		$this->assertEquals(new BasicMLData([0,0]), $pair->getInput());

		$pair->setInputArray([1,2]);
		$this->assertEquals(new BasicMLData([1,2]), $pair->getInput());

		$pair->setInputArray([2,1]);
		$this->assertEquals(new BasicMLData([2,1]), $pair->getInput());
	}

	public function testIsSupervised() {
		$this->assertFalse(BasicMLDataPair::createPair(1,0)->isSupervised());
		$this->assertTrue(BasicMLDataPair::createPair(1,1)->isSupervised());
	}

	public function testSignificance() {
		$pair = BasicMLDataPair::createPair(1,1);
		$this->assertEquals(1.0, $pair->getSignificance());
		$pair->setSignificance(0.5);
		$this->assertEquals(0.5, $pair->getSignificance());
	}
}
