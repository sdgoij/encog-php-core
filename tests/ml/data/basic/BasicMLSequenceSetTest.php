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
use encog\ml\data\basic\BasicMLDataSet;
use encog\ml\data\basic\BasicMLSequenceSet;
use encog\ml\data\MLDataError;
use PHPUnit\Framework\TestCase;
use RangeException;

class BasicMLSequenceSetTest extends TestCase {
	public function testCreateEmpty() {
		$seq = BasicMLSequenceSet::createEmpty();
		$this->assertEquals(false, $seq->isSupervised());
		$this->assertEquals(1, $seq->getSequenceCount());
		$this->assertEquals(0, $seq->getRecordCount());
		$this->assertEquals(0, $seq->getInputSize());
		$this->assertEquals(0, $seq->getIdealSize());
	}

	public function testCreateFromArray() {
		$seq = BasicMLSequenceSet::createFromArray([[1],[2],[3]]);
		$this->assertEquals(false, $seq->isSupervised());
		$this->assertEquals(1, $seq->getSequenceCount());
		$this->assertEquals(3, $seq->getRecordCount());
		$this->assertEquals(1, $seq->getInputSize());
		$this->assertEquals(0, $seq->getIdealSize());

		$seq = BasicMLSequenceSet::createFromArray([[1,2],[3,4],[5,6]], [[1],[2],[3]]);
		$this->assertEquals(true, $seq->isSupervised());
		$this->assertEquals(1, $seq->getSequenceCount());
		$this->assertEquals(3, $seq->getRecordCount());
		$this->assertEquals(2, $seq->getInputSize());
		$this->assertEquals(1, $seq->getIdealSize());
	}

	public function testCreateFromPairList() {
		$seq = BasicMLSequenceSet::createFromPairList([
			new BasicMLDataPair(new BasicMLData([1])),
			new BasicMLDataPair(new BasicMLData([2])),
			new BasicMLDataPair(new BasicMLData([3])),
		]);
		$this->assertEquals(false, $seq->isSupervised());
		$this->assertEquals(1, $seq->getSequenceCount());
		$this->assertEquals(3, $seq->getRecordCount());
		$this->assertEquals(1, $seq->getInputSize());
		$this->assertEquals(0, $seq->getIdealSize());

		$seq = BasicMLSequenceSet::createFromPairList([
			new BasicMLDataPair(new BasicMLData([1,2]), new BasicMLData([1])),
			new BasicMLDataPair(new BasicMLData([3,4]), new BasicMLData([2])),
			new BasicMLDataPair(new BasicMLData([5,6]), new BasicMLData([3])),
		]);
		$this->assertEquals(true, $seq->isSupervised());
		$this->assertEquals(1, $seq->getSequenceCount());
		$this->assertEquals(3, $seq->getRecordCount());
		$this->assertEquals(2, $seq->getInputSize());
		$this->assertEquals(1, $seq->getIdealSize());
	}

	public function testGetIdealSize() {
		$this->assertEquals(1, BasicMLSequenceSet::createFromArray([[1,2]], [[3]])->getIdealSize());
		$this->assertEquals(0, BasicMLSequenceSet::createFromArray([[1,2]])->getIdealSize());
	}

	public function testGetInputSize() {
		$this->assertEquals(2, BasicMLSequenceSet::createFromArray([[1,2]])->getInputSize());
		$this->assertEquals(1, BasicMLSequenceSet::createFromArray([[1]])->getInputSize());
	}

	public function testIsSupervised() {
		$this->assertEquals(true, BasicMLSequenceSet::createFromArray([[1,2]], [[3]])->isSupervised());
		$this->assertEquals(false, BasicMLSequenceSet::createFromArray([[1,2]])->isSupervised());
	}

	public function testGetRecordCount() {
		$seq = BasicMLSequenceSet::createFromArray([[1,2],[3,4]]);
		$this->assertEquals(2, $seq->getRecordCount());

		$seq->startNewSequence();
		$seq->add(new BasicMLData([5,6]));

		$this->assertEquals(3, $seq->getRecordCount());
		$this->assertEquals(3, $seq->size());
	}

	public function testStartNewSequence() {
		$pairs[] = new BasicMLDataPair(new BasicMLData([1,2]));
		$pairs[] = new BasicMLDataPair(new BasicMLData([3,4]));
		$seq = BasicMLSequenceSet::createFromPairList($pairs);

		$this->assertEquals(1, $seq->getSequenceCount());
		$this->assertEquals(2, $seq->getRecordCount());

		$seq->startNewSequence();
		$seq->addPair($pairs[1]);
		$seq->addPair($pairs[0]);

		$this->assertEquals(2, $seq->getSequenceCount());
		$this->assertEquals(4, $seq->getRecordCount());

		$this->assertEquals(new BasicMLDataSet(array_reverse($pairs)), $seq->getSequence(1));
		$this->assertEquals(new BasicMLDataSet($pairs), $seq->getSequence(0));

		$this->expectException(RangeException::class);
		$seq->getSequence(2);
	}

	public function testGetRecord() {
		$pairs[] = new BasicMLDataPair(new BasicMLData([1,2]));
		$pairs[] = new BasicMLDataPair(new BasicMLData([3,4]));
		$seq = BasicMLSequenceSet::createFromPairList($pairs);
		$seq->startNewSequence();
		$seq->addPair($pairs[1]);
		$seq->addPair($pairs[0]);

		$pair1 = BasicMLDataPair::createPair(2,0);
		$pair2 = BasicMLDataPair::createPair(2,0);
		$pair3 = BasicMLDataPair::createPair(2,0);
		$pair4 = BasicMLDataPair::createPair(2,0);

		$seq->getRecord(0, $pair1);
		$seq->getRecord(1, $pair2);
		$seq->getRecord(2, $pair3);
		$seq->getRecord(3, $pair4);

		$this->assertEquals($pairs[0], $pair1);
		$this->assertEquals($pairs[1], $pair2);
		$this->assertEquals($pairs[1], $pair3);
		$this->assertEquals($pairs[0], $pair4);

		$this->expectExceptionMessage("Record out of range: 4");
		$this->expectException(MLDataError::class);
		$seq->getRecord(4, BasicMLDataPair::createPair(1,0));
	}

	public function testGet() {
		$pairs[] = new BasicMLDataPair(new BasicMLData([1,2]));
		$pairs[] = new BasicMLDataPair(new BasicMLData([3,4]));
		$seq = BasicMLSequenceSet::createFromPairList($pairs);
		$seq->startNewSequence();
		$seq->addPair($pairs[1]);
		$seq->addPair($pairs[0]);

		$this->assertEquals($pairs[0], $seq->get(0));
		$this->assertEquals($pairs[1], $seq->get(1));
		$this->assertEquals($pairs[1], $seq->get(2));
		$this->assertEquals($pairs[0], $seq->get(3));

		$this->expectExceptionMessage("Record out of range: 4");
		$this->expectException(MLDataError::class);
		$seq->get(4);
	}

	public function testAdd() {
		$expect[] = new BasicMLDataSet([new BasicMLDataPair(new BasicMLData([1,2]))]);
		$expect[] = new BasicMLDataSet([new BasicMLDataPair(new BasicMLData([2,1]))]);

		$seq = BasicMLSequenceSet::createEmpty();
		$seq->add(new BasicMLData([1,2]));
		$seq->startNewSequence();
		$seq->add(new BasicMLData([2,1]));

		$this->assertEquals(2, $seq->getSequenceCount());
		$this->assertEquals(2, $seq->getRecordCount());
		$this->assertEquals($expect, $seq->getSequences());
	}

	public function testAddDataSet() {
		$seq = BasicMLSequenceSet::createEmpty();
		$seq->addDataSet(new BasicMLDataSet([[0,0], [0,1], [1,0], [1,1]]));
		$this->assertEquals(1, $seq->getSequenceCount());
		$this->assertEquals(4, $seq->getRecordCount());
	}

	public function testIterator() {
		$expect[] = new BasicMLDataPair(new BasicMLData([1,2]), new BasicMLData([3]));
		$expect[] = new BasicMLDataPair(new BasicMLData([4,5]), new BasicMLData([6]));
		$expect[] = new BasicMLDataPair(new BasicMLData([7,8]), new BasicMLData([9]));

		$seq = BasicMLSequenceSet::createEmpty();
		$seq->addPair($expect[0]);
		$seq->startNewSequence();
		$seq->addPair($expect[1]);
		$seq->startNewSequence();
		$seq->addPair($expect[2]);

		$this->assertEquals(3, $seq->getSequenceCount());
		$this->assertEquals(3, $seq->getRecordCount());
		$key = 0;

		foreach ($seq as $pair) {
			$this->assertEquals($expect[$key++], $pair);
		}
	}

	public function testOpenAdditional() {
		$seq1 = BasicMLSequenceSet::createFromArray([[1,2],[3,4],[5,6]], [[1],[2],[3]]);
		$seq2 = $seq1->openAdditional();

		$this->assertNotSame($seq1, $seq2);
		$this->assertEquals($seq1, $seq2);
	}
}
