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
namespace encog\test\ml\data\cross;

use encog\ml\data\cross\DataFold;
use encog\ml\data\versatile\MatrixMLDataSet;
use encog\ml\MLMethod;
use PHPUnit_Framework_TestCase as TestCase;

class DataFoldTest extends TestCase {
	public function testTrainingSet() {
		$expect = MatrixMLDataSet::createFromArray([[1,2],[3,4]], 1, 1);
		$fold = $this->createDataFold();
		$this->assertEquals($this->createDataFold()->getTraining(), $fold->getTraining());
		$fold->setTraining($expect);
		$this->assertEquals($expect, $fold->getTraining());
	}

	public function testValidationSet() {
		$expect = MatrixMLDataSet::createFromArray([[1,2],[3,4]], 1, 1);
		$fold = $this->createDataFold();
		$this->assertEquals($this->createDataFold()->getValidation(), $fold->getValidation());
		$fold->setValidation($expect);
		$this->assertEquals($expect, $fold->getValidation());
	}

	public function testMethod() {
		$expect = new class implements MLMethod{};
		$fold = $this->createDataFold();
		$this->assertNull($fold->getMethod());
		$fold->setMethod($expect);
		$this->assertEquals($expect, $fold->getMethod());
	}

	public function testScore() {
		$fold = $this->createDataFold();
		$this->assertEquals(INF, $fold->getScore());
		$fold->setScore(3);
		$this->assertEquals(3, $fold->getScore());
	}

	private function createDataFold() {
		return new DataFold(
			MatrixMLDataSet::createFromArray([[1,10],[2,20],[4,40],[5,50]], 1, 1),
			MatrixMLDataSet::createFromArray([[3,30],[6,60]], 1, 1)
		);
	}
}
