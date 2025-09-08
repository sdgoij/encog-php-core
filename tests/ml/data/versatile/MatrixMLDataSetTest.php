<?php
declare(strict_types=1);
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
namespace encog\test\ml\data\versatile;

use encog\EncogError;
use encog\ml\data\basic\BasicMLData;
use encog\ml\data\basic\BasicMLDataPair;
use encog\ml\data\versatile\MatrixMLDataSet;
use PHPUnit\Framework\TestCase;
use RangeException;
use Throwable;

class MatrixMLDataSetTest extends TestCase {
	public function testCreateFromArray() {
		$ds = MatrixMLDataSet::createFromArray(self::DATA, 1, 1);

		$this->assertEquals(self::DATA, $ds->getData());
		$this->assertEquals(1, $ds->getCalculatedInputSize());
		$this->assertEquals(1, $ds->getCalculatedIdealSize());
		$this->assertEquals([], $ds->getMask());
	}

	public function testCreateFromMatrixMLDataSet() {
		$ds = MatrixMLDataSet::createFromMatrixMLDataSet(MatrixMLDataSet::createFromArray(self::DATA, 1, 1));

		$this->assertEquals(self::DATA, $ds->getData());
		$this->assertEquals(1, $ds->getCalculatedInputSize());
		$this->assertEquals(1, $ds->getCalculatedIdealSize());
		$this->assertEquals([], $ds->getMask());

		$ds->setCalculatedInputSize(50);
		$ds->setCalculatedIdealSize(10);
		$ds->setData(range(1, 100, 1));
		$ds->setMask(range(1, 100, 2));

		$this->assertEquals(50, $ds->getCalculatedInputSize());
		$this->assertEquals(10, $ds->getCalculatedIdealSize());
		$this->assertEquals(range(1, 100, 2), $ds->getMask());
	}

	public function testGetRecordCount() {
		$ds1 = MatrixMLDataSet::createFromArray(self::DATA, 1, 1);
		$ds2 = MatrixMLDataSet::createFromArray(self::DATA, 1, 1, [0,2,4,6,8]);

		$this->assertEquals(10, $ds1->getRecordCount());
		$this->assertEquals(5, $ds2->getRecordCount());

		$this->expectExceptionMessage("DataSet must be normalized before using.");
		$this->expectException(EncogError::class);
		(new MatrixMLDataSet())->getRecordCount();
	}

	public function testGetIdealSize() {
		$ds = MatrixMLDataSet::createFromArray(self::DATA, 1, 1);
		$this->assertEquals(0, $ds->getIdealSize());
	}

	public function testGetInputSize() {
		$ds = MatrixMLDataSet::createFromArray(self::DATA, 1, 1);
		$this->assertEquals(0, $ds->getInputSize());
	}

	public function testIsSupervised() {
		$this->assertTrue(MatrixMLDataSet::createFromArray(self::DATA, 1, 1)->isSupervised());
	}

	public function testOpenAdditional() {
		$ds1 = MatrixMLDataSet::createFromArray(self::DATA, 1, 1);
		/** @var MatrixMLDataset */
		$ds2 = $ds1->openAdditional();

		$this->assertInstanceOf(MatrixMLDataSet::class, $ds2);
		$this->assertFalse($ds1 === $ds2);
		$this->assertEquals($ds1->size(), $ds2->size());
		$this->assertEquals($ds1->getCalculatedInputSize(), $ds2->getCalculatedInputSize());
		$this->assertEquals($ds1->getCalculatedIdealSize(), $ds2->getCalculatedIdealSize());
		$this->assertEquals($ds1->getMask(), $ds2->getMask());
	}

	public function testIterator() {
		foreach (MatrixMLDataSet::createFromArray(self::DATA, 1, 1) as $k => $v) {
			$pair = new BasicMLDataPair(
				new BasicMLData([self::DATA[$k][0]]),
				new BasicMLData([self::DATA[$k][1]])
			);
			$this->assertEquals($pair->getInput(), $v->getInput());
			$this->assertEquals($pair->getIdeal(), $v->getIdeal());
		}
	}

	public function testGetRecord() {
		$dataset = MatrixMLDataSet::createFromArray(self::DATA, 1, 1);
		$pair = BasicMLDataPair::createPair(1,1);

		$dataset->getRecord(0, $pair);
		$this->assertEquals([1], $pair->getInput()->getData()->toArray());
		$this->assertEquals([10], $pair->getIdeal()->getData()->toArray());

		try {
			$dataset->getRecord(10, $pair);
			$this->fail("Expected RangeException");
		} catch (RangeException $e) {
			$this->assertEquals("Index '10' out of bounds.", $e->getMessage());
		} catch (Throwable $e) {
			$this->fail("Unexpected Exception: " . $e->getMessage());
		}

		$this->expectExceptionMessage("DataSet must be normalized before using.");
		$this->expectException(EncogError::class);
		(new MatrixMLDataSet())->getRecord(1, $pair);
	}

	public function testTimeSeriesLead1Lag0() {
		$ds = MatrixMLDataSet::createFromArray(self::DATA, 1, 1);
		$ds->setLeadWindowSize(1);

		$this->assertEquals(9, $ds->size());

		$p1 = $ds->get(0);
		$this->assertEquals(1, $p1->getInput()->getDataAt(0));
		$this->assertEquals(20, $p1->getIdeal()->getDataAt(0));

		$p2 = $ds->get(1);
		$this->assertEquals(2, $p2->getInput()->getDataAt(0));
		$this->assertEquals(30, $p2->getIdeal()->getDataAt(0));

		$p3 = $ds->get(2);
		$this->assertEquals(3, $p3->getInput()->getDataAt(0));
		$this->assertEquals(40, $p3->getIdeal()->getDataAt(0));
	}

	public function testTimeSeriesLead0Lag1() {
		$ds = MatrixMLDataSet::createFromArray(self::DATA, 1, 1);
		$ds->setLagWindowSize(1);

		$this->assertEquals(9, $ds->size());

		$p1 = $ds->get(0);
		$this->assertEquals(1, $p1->getInput()->getDataAt(0));
		$this->assertEquals(2, $p1->getInput()->getDataAt(1));
		$this->assertEquals(10, $p1->getIdeal()->getDataAt(0));

		$p2 = $ds->get(1);
		$this->assertEquals(2, $p2->getInput()->getDataAt(0));
		$this->assertEquals(3, $p2->getInput()->getDataAt(1));
		$this->assertEquals(20, $p2->getIdeal()->getDataAt(0));

		$p3 = $ds->get(2);
		$this->assertEquals(3, $p3->getInput()->getDataAt(0));
		$this->assertEquals(4, $p3->getInput()->getDataAt(1));
		$this->assertEquals(30, $p3->getIdeal()->getDataAt(0));
	}

	public function testTimeSeriesLead1Lag1() {
		$ds = MatrixMLDataSet::createFromArray(self::DATA, 1, 1);
		$ds->setLeadWindowSize(1);
		$ds->setLagWindowSize(1);

		$this->assertEquals(8, $ds->size());

		$p1 = $ds->get(0);
		$this->assertEquals(1, $p1->getInput()->getDataAt(0));
		$this->assertEquals(2, $p1->getInput()->getDataAt(1));
		$this->assertEquals(20, $p1->getIdeal()->getDataAt(0));

		$p2 = $ds->get(1);
		$this->assertEquals(2, $p2->getInput()->getDataAt(0));
		$this->assertEquals(3, $p2->getInput()->getDataAt(1));
		$this->assertEquals(30, $p2->getIdeal()->getDataAt(0));

		$p3 = $ds->get(2);
		$this->assertEquals(3, $p3->getInput()->getDataAt(0));
		$this->assertEquals(4, $p3->getInput()->getDataAt(1));
		$this->assertEquals(40, $p3->getIdeal()->getDataAt(0));
	}

	public function testTimeSeriesLead2Lag1() {
		$ds = MatrixMLDataSet::createFromArray(self::DATA, 1, 1);
		$ds->setLeadWindowSize(2);
		$ds->setLagWindowSize(1);

		$this->assertEquals(7, $ds->size());

		$p1 = $ds->get(0);
		$this->assertEquals(1, $p1->getInput()->getDataAt(0));
		$this->assertEquals(2, $p1->getInput()->getDataAt(1));
		$this->assertEquals(20, $p1->getIdeal()->getDataAt(0));
		$this->assertEquals(30, $p1->getIdeal()->getDataAt(1));

		$p2 = $ds->get(1);
		$this->assertEquals(2, $p2->getInput()->getDataAt(0));
		$this->assertEquals(3, $p2->getInput()->getDataAt(1));
		$this->assertEquals(30, $p2->getIdeal()->getDataAt(0));
		$this->assertEquals(40, $p2->getIdeal()->getDataAt(1));

		$p3 = $ds->get(2);
		$this->assertEquals(3, $p3->getInput()->getDataAt(0));
		$this->assertEquals(4, $p3->getInput()->getDataAt(1));
		$this->assertEquals(40, $p3->getIdeal()->getDataAt(0));
		$this->assertEquals(50, $p3->getIdeal()->getDataAt(1));
	}

	public function testTimeSeriesLead1Lag2() {
		$ds = MatrixMLDataSet::createFromArray(self::DATA, 1, 1);
		$ds->setLeadWindowSize(1);
		$ds->setLagWindowSize(2);

		$this->assertEquals(7, $ds->size());

		$p1 = $ds->get(0);
		$this->assertEquals(1, $p1->getInput()->getDataAt(0));
		$this->assertEquals(2, $p1->getInput()->getDataAt(1));
		$this->assertEquals(3, $p1->getInput()->getDataAt(2));
		$this->assertEquals(20, $p1->getIdeal()->getDataAt(0));

		$p2 = $ds->get(1);
		$this->assertEquals(2, $p2->getInput()->getDataAt(0));
		$this->assertEquals(3, $p2->getInput()->getDataAt(1));
		$this->assertEquals(4, $p2->getInput()->getDataAt(2));
		$this->assertEquals(30, $p2->getIdeal()->getDataAt(0));

		$p3 = $ds->get(2);
		$this->assertEquals(3, $p3->getInput()->getDataAt(0));
		$this->assertEquals(4, $p3->getInput()->getDataAt(1));
		$this->assertEquals(5, $p3->getInput()->getDataAt(2));
		$this->assertEquals(40, $p3->getIdeal()->getDataAt(0));
	}

	const DATA = [
		[1, 10],
		[2, 20],
		[3, 30],
		[4, 40],
		[5, 50],
		[6, 60],
		[7, 70],
		[8, 80],
		[9, 90],
		[10, 100],
	];
}
