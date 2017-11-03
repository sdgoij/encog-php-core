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
namespace encog\test\ml\data\auto;

use encog\EncogError;
use encog\ml\data\auto\AutoFloatDataSet;
use encog\ml\data\basic\BasicMLData;
use encog\ml\data\basic\BasicMLDataPair;
use encog\ml\data\MLDataPair;
use encog\test\util\csv\MemoryStream;
use encog\util\csv\CSVFormat;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;

class AutoFloatDataSetTest extends TestCase {
	public function testGetIdealSize() {
		$this->assertEquals(0, (new AutoFloatDataSet(1,0,2,0))->getIdealSize());
		$this->assertEquals(0, (new AutoFloatDataSet(1,0,2,1))->getIdealSize());
		$this->assertEquals(0, (new AutoFloatDataSet(1,1,2,0))->getIdealSize());
		$this->assertEquals(1, (new AutoFloatDataSet(1,1,2,1))->getIdealSize());
		$this->assertEquals(2, (new AutoFloatDataSet(1,1,2,2))->getIdealSize());
		$this->assertEquals(3, (new AutoFloatDataSet(1,1,2,3))->getIdealSize());
		$this->assertEquals(6, (new AutoFloatDataSet(1,2,2,3))->getIdealSize());
	}

	public function testGetInputSize() {
		$this->assertEquals(0, (new AutoFloatDataSet(0,0,0,0))->getInputSize());
		$this->assertEquals(0, (new AutoFloatDataSet(0,0,1,0))->getInputSize());
		$this->assertEquals(0, (new AutoFloatDataSet(1,0,0,0))->getInputSize());
		$this->assertEquals(1, (new AutoFloatDataSet(1,0,1,0))->getInputSize());
		$this->assertEquals(2, (new AutoFloatDataSet(2,0,1,0))->getInputSize());
		$this->assertEquals(4, (new AutoFloatDataSet(2,0,2,0))->getInputSize());
	}

	public function testIsSupervised() {
		$this->assertFalse((new AutoFloatDataSet(1,0,2,0))->isSupervised());
		$this->assertFalse((new AutoFloatDataSet(1,0,2,1))->isSupervised());
		$this->assertFalse((new AutoFloatDataSet(1,1,2,0))->isSupervised());
		$this->assertTrue((new AutoFloatDataSet(1,1,2,1))->isSupervised());
	}

	public function testGetRecord() {
		$dataset = new AutoFloatDataSet(1,1,2,1);
		$dataset->addColumn([1,2,3,4,5]);
		$dataset->addColumn([1,2,3,4,5]);

		$pair = BasicMLDataPair::createPair(2, 1);

		$dataset->getRecord(0, $pair);
		$this->assertEquals(1.0, $pair->getInputArray()[0]);
		$this->assertEquals(2.0, $pair->getInputArray()[1]);
		$this->assertEquals(3.0, $pair->getIdealArray()[0]);

		$dataset->getRecord(1, $pair);
		$this->assertEquals(2.0, $pair->getInputArray()[0]);
		$this->assertEquals(3.0, $pair->getInputArray()[1]);
		$this->assertEquals(4.0, $pair->getIdealArray()[0]);

		$dataset->getRecord(2, $pair);
		$this->assertEquals(3.0, $pair->getInputArray()[0]);
		$this->assertEquals(4.0, $pair->getInputArray()[1]);
		$this->assertEquals(5.0, $pair->getIdealArray()[0]);

		$dataset->setNormalizationEnabled(true);

		$dataset->getRecord(0, $pair);
		$this->assertEquals(-1.0, $pair->getInputArray()[0]);
		$this->assertEquals(-0.5, $pair->getInputArray()[1]);
		$this->assertEquals(0.0, $pair->getIdealArray()[0]);

		$dataset->getRecord(1, $pair);
		$this->assertEquals(-0.5, $pair->getInputArray()[0]);
		$this->assertEquals(0.0, $pair->getInputArray()[1]);
		$this->assertEquals(0.5, $pair->getIdealArray()[0]);

		$dataset->getRecord(2, $pair);
		$this->assertEquals(0.0, $pair->getInputArray()[0]);
		$this->assertEquals(0.5, $pair->getInputArray()[1]);
		$this->assertEquals(1.0, $pair->getIdealArray()[0]);

		$this->expectException(OutOfBoundsException::class);
		$this->expectExceptionMessage("5");
		$dataset->getRecord(3, $pair);
	}

	public function testRecordCount() {
		$dataset = new AutoFloatDataSet(1,1,2,1);
		$this->assertEquals($dataset->size(), $dataset->getRecordCount());
		$this->assertEquals(0, $dataset->getRecordCount());

		$dataset->addColumn([1,2,3,4,5]);

		$this->assertEquals($dataset->getRecordCount(), $dataset->size());
		$this->assertEquals(3, $dataset->size());
	}

	public function testNormalizedMinMax() {
		$dataset = new AutoFloatDataSet(1,1,2,1);

		$this->assertEquals(-1.0, $dataset->getNormalizedMin());
		$this->assertEquals(1.0, $dataset->getNormalizedMax());

		$dataset->setNormalizedMin(-0.5);
		$dataset->setNormalizedMax(0.5);

		$this->assertEquals(-0.5, $dataset->getNormalizedMin());
		$this->assertEquals(0.5, $dataset->getNormalizedMax());
	}

	public function testNormalizationEnabled() {
		$dataset = new AutoFloatDataSet(1,1,2,1);
		$this->assertFalse($dataset->isNormalizationEnabled());
		$dataset->setNormalizationEnabled(true);
		$this->assertTrue($dataset->isNormalizationEnabled());
	}

	public function testAdd() {
		$this->expectExceptionMessage("Add's not supported by this dataset.");
		$this->expectException(EncogError::class);

		(new AutoFloatDataSet(1,1,2,1))->add(new BasicMLData());
	}

	public function testAddPair() {
		$this->expectExceptionMessage("Add's not supported by this dataset.");
		$this->expectException(EncogError::class);

		(new AutoFloatDataSet(1,1,2,1))->addPair(BasicMLDataPair::createPair(2,1));
	}

	public function testGetPair() {
		$dataset = new AutoFloatDataSet(1,1,2,1);
		$dataset->addColumn([1,2,3,4,5]);
		$dataset->addColumn([1,2,3,4,5]);

		$pairs[] = BasicMLDataPair::createPair(2, 1);
		$pairs[] = BasicMLDataPair::createPair(2, 1);
		$pairs[] = BasicMLDataPair::createPair(2, 1);

		$dataset->getRecord(0, $pairs[0]);
		$dataset->getRecord(1, $pairs[1]);
		$dataset->getRecord(2, $pairs[2]);

		$this->assertEquals($pairs[0], $dataset->get(0));
		$this->assertEquals($pairs[1], $dataset->get(1));
		$this->assertEquals($pairs[2], $dataset->get(2));

		$this->expectException(OutOfBoundsException::class);
		$this->expectExceptionMessage("42");
		$dataset->get(42);
	}

	public function testOpenAdditional() {
		$dataset = new AutoFloatDataSet(1,1,2,1);
		$this->assertFalse($dataset === $dataset->openAdditional());
	}

	public function testIterator() {
		$dataset = new AutoFloatDataSet(1,1,2,1);
		$dataset->addColumn([1,2,3,4,5]);
		$dataset->addColumn([1,2,3,4,5]);

		/** @var MLDataPair $pair */
		foreach ($dataset as $key => $pair) {
			$this->assertEquals(1.0+$key%5, $pair->getInputArray()[0]);
			$this->assertEquals(2.0+$key%5, $pair->getInputArray()[1]);
			$this->assertEquals(3.0+$key%5, $pair->getIdealArray()[0]);
		}
	}

	public function testLoadCSV() {
		MemoryStream::put("12345.csv", "1,2,3,4,5\r\n1,2,3,4,5");

		$dataset = new AutoFloatDataSet(1,2,1,1);
		$dataset->loadCSV("memory://12345.csv", false, CSVFormat::English(), [0,1], [2]);
		$this->assertEquals(1, $dataset->getRecordCount());
	}
}
