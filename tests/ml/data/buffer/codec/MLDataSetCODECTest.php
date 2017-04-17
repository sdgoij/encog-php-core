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
namespace encog\test\ml\data\buffer\codec;

use encog\ml\data\basic\BasicMLData;
use encog\ml\data\basic\BasicMLDataSet;
use encog\ml\data\buffer\codec\MLDataSetCODEC;
use encog\ml\data\MLDataSet;
use PHPUnit_Framework_TestCase as TestCase;

class MLDataSetCODECTest extends TestCase {
	public function testIdealSize() {
		$this->assertEquals(0, (new MLDataSetCODEC($this->createUnsupervisedDataSet()))->getIdealSize());
		$this->assertEquals(1, (new MLDataSetCODEC($this->createSupervisedDataSet()))->getIdealSize());
	}

	public function testInputSize() {
		$this->assertEquals(3, (new MLDataSetCODEC($this->createUnsupervisedDataSet()))->getInputSize());
		$this->assertEquals(2, (new MLDataSetCODEC($this->createSupervisedDataSet()))->getInputSize());
	}

	public function testRead() {
		$input = $ideal = [];
		$significance = 0.0;
		$index = 0;

		$codec = new MLDataSetCODEC($this->createSupervisedDataSet());
		$this->assertFalse($codec->read($input, $ideal, $significance));
		$codec->prepareRead();

		while ($codec->read($input, $ideal, $significance)) {
			$this->assertEquals(1+$index, $input[0]);
			$this->assertEquals(2+$index, $input[1]);
			$this->assertEquals(3+$index, $ideal[0]);
			$this->assertEquals(1.0, $significance);
			$index++;
		}
	}

	public function testWrite() {
		$dataset = new BasicMLDataSet();
		$codec = new MLDataSetCODEC($dataset);
		$codec->prepareWrite(3, 2, 1);
		$codec->write([1,2], [3], 1.0);
		$codec->write([2,3], [4], 1.0);
		$codec->write([3,4], [5], 1.0);

		$this->assertEquals($this->createSupervisedDataSet(), $dataset);
	}

	private function createSupervisedDataSet(): MLDataSet {
		return new BasicMLDataSet(
			[
				new BasicMLData([1,2]),
				new BasicMLData([2,3]),
				new BasicMLData([3,4]),
			],
			[
				new BasicMLData([3]),
				new BasicMLData([4]),
				new BasicMLData([5]),
			]
		);
	}

	private function createUnsupervisedDataSet(): MLDataSet {
		return new BasicMLDataSet(
			[
				new BasicMLData([1,2,3]),
				new BasicMLData([2,3,4]),
				new BasicMLData([3,4,5]),
			]
		);
	}
}
