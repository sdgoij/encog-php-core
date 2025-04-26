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
namespace encog\test\ml\data\folded;

use encog\ml\data\basic\BasicMLData;
use encog\ml\data\basic\BasicMLDataPair;
use encog\ml\data\basic\BasicMLDataSet;
use encog\ml\data\folded\FoldedDataSet;
use encog\ml\data\MLDataError;
use encog\ml\data\MLDataPair;
use encog\ml\data\MLDataSet;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RangeException;

class FoldedDataSetTest extends TestCase {
	public function testCreateFoldedDataSet() {
		$this->assertEquals($this->createUnsupervisedDataSet(),
			(new FoldedDataSet($this->createUnsupervisedDataSet()))->getDataSet());
		$this->assertEquals($this->createSupervisedDataSet(),
			(new FoldedDataSet($this->createSupervisedDataSet()))->getDataSet());

		$this->expectExceptionMessage("Cannot fold empty dataset.");
		$this->expectException(MLDataError::class);

		(new FoldedDataSet(new BasicMLDataSet()))->getDataSet();
	}

	public function testCurrentFold() {
		$dataset = new FoldedDataSet($this->createSupervisedDataSet(), 3);
		$this->assertEquals(0, $dataset->getCurrentFold());
		$this->assertEquals(0, $dataset->getCurrentFoldOffset());
		$this->assertEquals(1, $dataset->getCurrentFoldSize());

		$dataset->setCurrentFold(1);
		$this->assertEquals(1, $dataset->getCurrentFold());
		$this->assertEquals(1, $dataset->getCurrentFoldOffset());
		$this->assertEquals(1, $dataset->getCurrentFoldSize());

		$dataset->setCurrentFold(2);
		$this->assertEquals(2, $dataset->getCurrentFold());
		$this->assertEquals(2, $dataset->getCurrentFoldOffset());
		$this->assertEquals(1, $dataset->getCurrentFoldSize());

		$dataset2 = clone $dataset;
		$dataset2->setOwner($dataset);
		$this->assertEquals(2, $dataset2->getCurrentFold());
		$this->assertEquals(2, $dataset2->getCurrentFoldOffset());
		$this->assertEquals(1, $dataset2->getCurrentFoldSize());

		$this->expectExceptionMessage("Can't set the current fold to be greater than the number of folds.");
		$this->expectException(MLDataError::class);

		$dataset->setCurrentFold(3);
	}

	public function testClose() {
		/** @var MLDataSet|MockObject $dataset */
		$dataset = $this->getMockBuilder(BasicMLDataSet::class)->onlyMethods(['close', 'getRecordCount'])->getMock();
		$dataset->expects($this->atLeastOnce())->method('getRecordCount')->will($this->returnValue(1));
		$dataset->expects($this->once())->method('close');
		(new FoldedDataSet($dataset))->close();
	}

	public function testGetIdealSize() {
		$this->assertEquals(0, (new FoldedDataSet($this->createUnsupervisedDataSet()))->getIdealSize());
		$this->assertEquals(1, (new FoldedDataSet($this->createSupervisedDataSet()))->getIdealSize());
	}

	public function testGetInputSize() {
		$this->assertEquals(3, (new FoldedDataSet($this->createUnsupervisedDataSet()))->getInputSize());
		$this->assertEquals(2, (new FoldedDataSet($this->createSupervisedDataSet()))->getInputSize());
	}

	public function testIsSupervised() {
		$this->assertFalse((new FoldedDataSet($this->createUnsupervisedDataSet()))->isSupervised());
		$this->assertTrue((new FoldedDataSet($this->createSupervisedDataSet()))->isSupervised());
	}

	public function testRecordCount() {
		$dataset = new FoldedDataSet($this->createSupervisedDataSet());
		$this->assertEquals($dataset->getRecordCount(), $dataset->size());
		$this->assertEquals(3, $dataset->getRecordCount());

		$dataset = new FoldedDataSet($this->createSupervisedDataSet(), 2);
		$this->assertEquals(1, $dataset->getRecordCount());

		$dataset->setCurrentFold(1);
		$this->assertEquals(2, $dataset->getRecordCount());
	}

	public function testGetRecord() {
		$dataset = new FoldedDataSet($this->createSupervisedDataSet());
		$pair = BasicMLDataPair::createPair(2, 1);

		$dataset->getRecord(0, $pair);
		$this->assertEquals(1, $pair->getInputArray()[0]);
		$this->assertEquals(2, $pair->getInputArray()[1]);
		$this->assertEquals(3, $pair->getIdealArray()[0]);

		$dataset->getRecord(1, $pair);
		$this->assertEquals(2, $pair->getInputArray()[0]);
		$this->assertEquals(3, $pair->getInputArray()[1]);
		$this->assertEquals(4, $pair->getIdealArray()[0]);

		$dataset->getRecord(2, $pair);
		$this->assertEquals(3, $pair->getInputArray()[0]);
		$this->assertEquals(4, $pair->getInputArray()[1]);
		$this->assertEquals(5, $pair->getIdealArray()[0]);

		$dataset = new FoldedDataSet($this->createSupervisedDataSet(), 2);

		$dataset->getRecord(0, $pair);
		$this->assertEquals(1, $pair->getInputArray()[0]);
		$this->assertEquals(2, $pair->getInputArray()[1]);
		$this->assertEquals(3, $pair->getIdealArray()[0]);

		$dataset->setCurrentFold(1);
		$dataset->getRecord(0, $pair);

		$this->assertEquals(2, $pair->getInputArray()[0]);
		$this->assertEquals(3, $pair->getInputArray()[1]);
		$this->assertEquals(4, $pair->getIdealArray()[0]);

		$dataset->getRecord(1, $pair);
		$this->assertEquals(3, $pair->getInputArray()[0]);
		$this->assertEquals(4, $pair->getInputArray()[1]);
		$this->assertEquals(5, $pair->getIdealArray()[0]);

		$this->expectException(RangeException::class);
		$dataset->getRecord(2, $pair);
	}

	public function testIterator() {
		$dataset = new FoldedDataSet($this->createSupervisedDataSet());
		/** @var MLDataPair $pair */
		foreach ($dataset as $key => $pair) {
			$this->assertEquals(1+$key, $pair->getInputArray()[0]);
			$this->assertEquals(2+$key, $pair->getInputArray()[1]);
			$this->assertEquals(3+$key, $pair->getIdealArray()[0]);
		}
		$dataset->fold(3);

		for ($i = 0; $i < 3; $i++) {
			$dataset->setCurrentFold($i);
			foreach ($dataset as $pair) {
				$this->assertEquals(1+$i, $pair->getInputArray()[0]);
				$this->assertEquals(2+$i, $pair->getInputArray()[1]);
				$this->assertEquals(3+$i, $pair->getIdealArray()[0]);
			}
		}
	}

	public function testOwner() {
		$dataset1 = new FoldedDataSet($this->createUnsupervisedDataSet());
		$dataset2 = clone $dataset1;
		$dataset2->setOwner($dataset1);

		$this->assertEquals($dataset1, $dataset2->getOwner());
		$this->assertNull($dataset1->getOwner());
	}

	public function testAdd() {
		$this->expectExceptionMessage("Direct adds to the folded dataset are not supported.");
		$this->expectException(MLDataError::class);

		(new FoldedDataSet($this->createUnsupervisedDataSet()))->add(new BasicMLData([1,2,3]));
	}

	public function testAddPair() {
		$this->expectExceptionMessage("Direct adds to the folded dataset are not supported.");
		$this->expectException(MLDataError::class);

		(new FoldedDataSet($this->createSupervisedDataSet()))->addPair(BasicMLDataPair::createPair(2,1));
	}

	public function testOpenAdditional() {
		$dataset1 = new FoldedDataSet($this->createUnsupervisedDataSet());
		$dataset2 = $dataset1->openAdditional();
		$this->assertFalse($dataset1 === $dataset2);
		$this->assertInstanceOf(FoldedDataSet::class, $dataset2);
		if (!$dataset2 instanceof FoldedDataSet) $this->fail("Expected FoldedDataSet.");
		$this->expectExceptionMessage("Can't set the fold on a non-top-level set.");
		$this->expectException(MLDataError::class);
		$dataset2->setCurrentFold(2);
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
