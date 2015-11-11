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
namespace encog\test\ml\data\temporal;

use DateTime;
use encog\engine\network\activation\ActivationTANH;
use encog\ml\data\basic\BasicMLData;
use encog\ml\data\basic\BasicMLDataPair;
use encog\ml\data\MLDataPair;
use encog\ml\data\temporal\TemporalDataDescription;
use encog\ml\data\temporal\TemporalDataType;
use encog\ml\data\temporal\TemporalError;
use encog\ml\data\temporal\TemporalMLDataSet;
use encog\ml\data\temporal\TemporalPoint;
use encog\util\time\TimeUnit;
use PHPUnit_Framework_TestCase as TestCase;

class TemporalMLDataSetTest extends TestCase {
	public function testAdd() {
		$this->expectExceptionMessage("Direct adds to the temporal dataset are not supported. "
			. "Add TemporalPoint objects and call generate.");
		$this->expectException(TemporalError::class);
		(new TemporalMLDataSet(2,1))->add(new BasicMLData([1,2,3]));
	}

	public function testAddPair() {
		$this->expectExceptionMessage("Direct adds to the temporal dataset are not supported. "
			. "Add TemporalPoint objects and call generate.");
		$this->expectException(TemporalError::class);
		(new TemporalMLDataSet(2,1))->addPair(BasicMLDataPair::createPair(2,1));
	}

	public function testAddDescription() {
		$temporal = new TemporalMLDataSet(5, 1);
		$this->assertEquals(0, $temporal->getInputNeuronCount());
		$this->assertEquals(0, $temporal->getOutputNeuronCount());

		$p1 = new TemporalDataDescription(TemporalDataType::$RAW, true, false);
		$p2 = new TemporalDataDescription(TemporalDataType::$RAW, true, false);
		$p3 = new TemporalDataDescription(TemporalDataType::$RAW, false, true);

		$p1->setIndex(0);
		$p2->setIndex(1);
		$p3->setIndex(2);

		$temporal->addDescription(new TemporalDataDescription(TemporalDataType::$RAW, true, false));
		$this->assertEquals(5, $temporal->getInputNeuronCount());
		$this->assertEquals(0, $temporal->getOutputNeuronCount());

		$temporal->addDescription(new TemporalDataDescription(TemporalDataType::$RAW, true, false));
		$temporal->addDescription(new TemporalDataDescription(TemporalDataType::$RAW, false, true));

		$this->assertEquals([$p1, $p2, $p3], $temporal->getDescriptions());
		$this->assertEquals(10, $temporal->getInputNeuronCount());
		$this->assertEquals(1, $temporal->getOutputNeuronCount());

		$this->expectExceptionMessage("Can't add anymore descriptions, there are already temporal points defined.");
		$this->expectException(TemporalError::class);

		$temporal = new TemporalMLDataSet(5, 1);
		$temporal->createPoint(0);
		$temporal->addDescription(new TemporalDataDescription(TemporalDataType::$RAW, true, false));
	}

	public function testClear() {
		$temporal = new TemporalMLDataSet(5, 1);
		$temporal->addDescription(new TemporalDataDescription(TemporalDataType::$RAW, true, false));
		$temporal->addDescription(new TemporalDataDescription(TemporalDataType::$RAW, true, false));
		$temporal->addDescription(new TemporalDataDescription(TemporalDataType::$RAW, false, true));
		$temporal->createPoint(0);
		$temporal->createPoint(1);
		$temporal->createPoint(2);
		$temporal->setData([1,2,3]);
		$temporal->clear();

		$this->assertEquals([], $temporal->getDescriptions());
		$this->assertEquals([], $temporal->getPoints());
		$this->assertEquals([], $temporal->getData());
	}

	public function testCreatePoint() {
		$temporal = new TemporalMLDataSet(5, 1);
		$temporal->addDescription(new TemporalDataDescription(TemporalDataType::$RAW, true, false));
		$temporal->addDescription(new TemporalDataDescription(TemporalDataType::$RAW, true, false));
		$temporal->addDescription(new TemporalDataDescription(TemporalDataType::$RAW, false, true));
		$point = $temporal->createPoint(1);

		$this->assertEquals(1, $point->getSequence());
		$this->assertEquals(3, count($point->getData()));
	}

	public function testCreatePointDate() {
		$temporal = new TemporalMLDataSet(2, 1);
		$temporal->createPointDate(new DateTime("20180706"));
		$temporal->createPointDate(new DateTime("20180707"));
		$temporal->createPointDate(new DateTime("20180708"));

		/** @var TemporalPoint $point */
		foreach ($temporal->getPoints() as $key => $point) {
			$this->assertEquals($key, $point->getSequence());
		}
	}

	public function testSortPoints() {
		$temporal = new TemporalMLDataSet(2, 1);
		$temporal->createPoint(2);
		$temporal->createPoint(0);
		$temporal->createPoint(3);
		$temporal->createPoint(1);
		$temporal->sortPoints();

		/** @var TemporalPoint $point */
		foreach ($temporal->getPoints() as $key => $point) {
			$this->assertEquals($key, $point->getSequence());
		}
	}

	public function testSequenceGranularity() {
		$temporal = new TemporalMLDataSet(2, 1);
		$years = new TimeUnit(TimeUnit::YEARS);
		$days = new TimeUnit(TimeUnit::DAYS);

		$this->assertEquals($days, $temporal->getSequenceGranularity());

		$temporal->setSequenceGranularity($years);

		$this->assertEquals($years, $temporal->getSequenceGranularity());
	}

	public function testHighLowSequence() {
		$temporal = new TemporalMLDataSet(2, 1);

		$this->assertEquals(PHP_INT_MAX, $temporal->getHighSequence());
		$this->assertEquals(PHP_INT_MIN, $temporal->getLowSequence());

		$temporal->setHighSequence(100);
		$temporal->setLowSequence(0);

		$this->assertEquals(100, $temporal->getHighSequence());
		$this->assertEquals(0, $temporal->getLowSequence());
	}

	public function testPointInRange() {
		$temporal = new TemporalMLDataSet(2, 1);
		$points[] = $temporal->createPoint(-1);
		$points[] = $temporal->createPoint(0);
		$points[] = $temporal->createPoint(1);
		$points[] = $temporal->createPoint(10);

		$this->assertTrue($temporal->isPointInRange($points[0]));
		$this->assertTrue($temporal->isPointInRange($points[1]));
		$this->assertTrue($temporal->isPointInRange($points[2]));
		$this->assertTrue($temporal->isPointInRange($points[3]));

		$temporal->setHighSequence(9);
		$temporal->setLowSequence(0);

		$this->assertFalse($temporal->isPointInRange($points[0]));
		$this->assertTrue($temporal->isPointInRange($points[1]));
		$this->assertTrue($temporal->isPointInRange($points[2]));
		$this->assertFalse($temporal->isPointInRange($points[3]));
	}

	public function testCalculatePointsInRange() {
		$temporal = new TemporalMLDataSet(2, 1);
		$temporal->createPoint(-1);
		$temporal->createPoint(0);
		$temporal->createPoint(1);
		$temporal->createPoint(10);

		$this->assertEquals(4, $temporal->calculatePointsInRange());

		$temporal->setHighSequence(9);
		$temporal->setLowSequence(0);

		$this->assertEquals(2, $temporal->calculatePointsInRange());
	}

	public function testCalculateStartIndex() {
		$temporal = new TemporalMLDataSet(2, 1);
		$this->assertEquals(-1, $temporal->calculateStartIndex());

		$temporal->createPoint(-1);
		$temporal->createPoint(0);
		$temporal->createPoint(1);
		$temporal->createPoint(10);

		$this->assertEquals(0, $temporal->calculateStartIndex());

		$temporal->setLowSequence(1);

		$this->assertEquals(2, $temporal->calculateStartIndex());
	}

	public function testCalculateActualSetSize() {
		$temporal = new TemporalMLDataSet(2,1);
		$this->assertEquals(0, $temporal->calculateActualSetSize());
		$temporal->createPoint(-1);
		$temporal->createPoint(0);
		$temporal->createPoint(1);
		$temporal->createPoint(2);
		$temporal->createPoint(10);
		$temporal->createPoint(11);
		$this->assertEquals(6, $temporal->calculateActualSetSize());
		$temporal->setDesiredSetSize(5);
		$this->assertEquals($temporal->getDesiredSetSize(), $temporal->calculateActualSetSize());
		$this->assertEquals(5, $temporal->calculateActualSetSize());
	}

	public function testGenerateInputData() {
		$this->expectExceptionMessage("Unsupported data type.");
		$this->expectException(TemporalError::class);

		$temporal = new TemporalMLDataSet(2, 1);
		$temporal->addDescription(new TemporalDataDescription(new TemporalDataType(0), true, false));
		$temporal->generateInputData(0);
	}

	public function testGenerateOutputData() {
		$temporal = new TemporalMLDataSet(2, 1);
		$temporal->addDescription(new TemporalDataDescription(TemporalDataType::$RAW, true, false));
		$temporal->addDescription(new TemporalDataDescription(TemporalDataType::$RAW, false, true));
		$temporal->createPoint(0);
		$temporal->createPoint(1);
		$temporal->createPoint(2);

		$this->assertEquals(0.0, $temporal->generateOutputData(1)->getDataAt(0));
		$this->assertEquals(0.0, $temporal->generateOutputData(2)->getDataAt(0));

		$this->expectExceptionMessage("Can't generate prediction temporal data beyond the end of provided data.");
		$this->expectException(TemporalError::class);
		$temporal->generateOutputData(3);
	}

	public function testStartingPoint() {
		$temporal = new TemporalMLDataSet(2, 1);
		$this->assertNull($temporal->getStartingPoint());
		$temporal->setStartingPoint(new DateTime("20201212"));
		$this->assertEquals(new DateTime("20201212"), $temporal->getStartingPoint());
	}

	public function testGetSequenceFromDate() {
		$temporal = new TemporalMLDataSet(5, 1);
		$this->assertEquals(0, $temporal->getSequenceFromDate(new DateTime('20010101')));
		$this->assertEquals(1, $temporal->getSequenceFromDate(new DateTime('20010102')));
		$this->assertEquals(366, $temporal->getSequenceFromDate(new DateTime('20020102')));
	}

	public function testFirstDeltaChange() {
		$temporal = new TemporalMLDataSet(5, 1);
		$temporal->addDescription(new TemporalDataDescription(TemporalDataType::$DELTA_CHANGE, true, false));
		$temporal->addDescription(new TemporalDataDescription(TemporalDataType::$PERCENT_CHANGE, true, false));
		$temporal->createPoint(0);
		$temporal->createPoint(1);
		$temporal->generate();
	}

	public function testWindowSize() {
		$temporal = new TemporalMLDataSet(2, 1);

		$this->assertEquals(2, $temporal->getInputWindowSize());
		$this->assertEquals(1, $temporal->getPredictWindowSize());

		$temporal->setInputWindowSize(4);
		$temporal->setPredictWindowSize(2);

		$this->assertEquals(4, $temporal->getInputWindowSize());
		$this->assertEquals(2, $temporal->getPredictWindowSize());
	}

	public function testBasicTemporal() {
		$temporal = new TemporalMLDataSet(5, 1);
		$temporal->addDescription(new TemporalDataDescription(TemporalDataType::$RAW, true, false));
		$temporal->addDescription(new TemporalDataDescription(TemporalDataType::$RAW, true, false));
		$temporal->addDescription(new TemporalDataDescription(TemporalDataType::$RAW, false, true));
		for ($i = 0; $i < 10; $i++) {
			$point = $temporal->createPoint($i);
			$point->setDataAt(0, 1+($i*3));
			$point->setDataAt(1, 2+($i*3));
			$point->setDataAt(2, 3+($i*3));
		}
		$temporal->generate();

		$this->assertEquals(10, $temporal->calculateActualSetSize());
		$this->assertEquals(10, $temporal->getInputNeuronCount());
		$this->assertEquals(1, $temporal->getOutputNeuronCount());

		$iterator = $temporal->getIterator();

		/** @var MLDataPair $pair */
		$pair = $iterator->current();
		$this->assertEquals(10, $pair->getInput()->size());
		$this->assertEquals(1, $pair->getIdeal()->size());
		$this->assertEquals(1.0, $pair->getInput()->getDataAt(0));
		$this->assertEquals(2.0, $pair->getInput()->getDataAt(1));
		$this->assertEquals(4.0, $pair->getInput()->getDataAt(2));
		$this->assertEquals(5.0, $pair->getInput()->getDataAt(3));
		$this->assertEquals(7.0, $pair->getInput()->getDataAt(4));
		$this->assertEquals(8.0, $pair->getInput()->getDataAt(5));
		$this->assertEquals(10.0, $pair->getInput()->getDataAt(6));
		$this->assertEquals(11.0, $pair->getInput()->getDataAt(7));
		$this->assertEquals(13.0, $pair->getInput()->getDataAt(8));
		$this->assertEquals(14.0, $pair->getInput()->getDataAt(9));
		$this->assertEquals(18.0, $pair->getIdeal()->getDataAt(0));

		$iterator->next();
		$this->assertTrue($iterator->valid());

		$pair = $iterator->current();
		$this->assertEquals(10, $pair->getInput()->size());
		$this->assertEquals(1, $pair->getIdeal()->size());
		$this->assertEquals(4.0, $pair->getInput()->getDataAt(0));
		$this->assertEquals(5.0, $pair->getInput()->getDataAt(1));
		$this->assertEquals(7.0, $pair->getInput()->getDataAt(2));
		$this->assertEquals(8.0, $pair->getInput()->getDataAt(3));
		$this->assertEquals(10.0, $pair->getInput()->getDataAt(4));
		$this->assertEquals(11.0, $pair->getInput()->getDataAt(5));
		$this->assertEquals(13.0, $pair->getInput()->getDataAt(6));
		$this->assertEquals(14.0, $pair->getInput()->getDataAt(7));
		$this->assertEquals(16.0, $pair->getInput()->getDataAt(8));
		$this->assertEquals(17.0, $pair->getInput()->getDataAt(9));
		$this->assertEquals(21.0, $pair->getIdeal()->getDataAt(0));

		$iterator->next();
		$this->assertTrue($iterator->valid());

		$pair = $iterator->current();
		$this->assertEquals(10, $pair->getInput()->size());
		$this->assertEquals(1, $pair->getIdeal()->size());
		$this->assertEquals(7.0, $pair->getInput()->getDataAt(0));
		$this->assertEquals(8.0, $pair->getInput()->getDataAt(1));
		$this->assertEquals(10.0, $pair->getInput()->getDataAt(2));
		$this->assertEquals(11.0, $pair->getInput()->getDataAt(3));
		$this->assertEquals(13.0, $pair->getInput()->getDataAt(4));
		$this->assertEquals(14.0, $pair->getInput()->getDataAt(5));
		$this->assertEquals(16.0, $pair->getInput()->getDataAt(6));
		$this->assertEquals(17.0, $pair->getInput()->getDataAt(7));
		$this->assertEquals(19.0, $pair->getInput()->getDataAt(8));
		$this->assertEquals(20.0, $pair->getInput()->getDataAt(9));
		$this->assertEquals(24.0, $pair->getIdeal()->getDataAt(0));

		$iterator->next();
		$this->assertTrue($iterator->valid());

		$pair = $iterator->current();
		$this->assertEquals(10, $pair->getInput()->size());
		$this->assertEquals(1, $pair->getIdeal()->size());
		$this->assertEquals(10.0, $pair->getInput()->getDataAt(0));
		$this->assertEquals(11.0, $pair->getInput()->getDataAt(1));
		$this->assertEquals(13.0, $pair->getInput()->getDataAt(2));
		$this->assertEquals(14.0, $pair->getInput()->getDataAt(3));
		$this->assertEquals(16.0, $pair->getInput()->getDataAt(4));
		$this->assertEquals(17.0, $pair->getInput()->getDataAt(5));
		$this->assertEquals(19.0, $pair->getInput()->getDataAt(6));
		$this->assertEquals(20.0, $pair->getInput()->getDataAt(7));
		$this->assertEquals(22.0, $pair->getInput()->getDataAt(8));
		$this->assertEquals(23.0, $pair->getInput()->getDataAt(9));
		$this->assertEquals(27.0, $pair->getIdeal()->getDataAt(0));

		$iterator->next();
		$this->assertFalse($iterator->valid());
	}

	public function testHiLowTemporal() {
		$temporal = new TemporalMLDataSet(5, 1);
		$temporal->addDescription(new TemporalDataDescription(TemporalDataType::$RAW, true, false));
		$temporal->addDescription(new TemporalDataDescription(TemporalDataType::$RAW, true, false));
		$temporal->addDescription(new TemporalDataDescription(TemporalDataType::$RAW, false, true));
		for ($i = 0; $i < 10; $i++) {
			$point = $temporal->createPoint($i);
			$point->setDataAt(0, 1+($i*3));
			$point->setDataAt(1, 2+($i*3));
			$point->setDataAt(2, 3+($i*3));
		}
		$temporal->setHighSequence(8);
		$temporal->setLowSequence(2);
		$temporal->generate();

		$this->assertEquals(7, $temporal->calculateActualSetSize());
		$this->assertEquals(10, $temporal->getInputNeuronCount());
		$this->assertEquals(1, $temporal->getOutputNeuronCount());

		$iterator = $temporal->getIterator();
		/** @var MLDataPair $pair */
		$pair = $iterator->current();

		$this->assertEquals(10, $pair->getInput()->size());
		$this->assertEquals(1, $pair->getIdeal()->size());
		$this->assertEquals(7.0, $pair->getInput()->getDataAt(0));
		$this->assertEquals(8.0, $pair->getInput()->getDataAt(1));
		$this->assertEquals(10.0, $pair->getInput()->getDataAt(2));
		$this->assertEquals(11.0, $pair->getInput()->getDataAt(3));
		$this->assertEquals(13.0, $pair->getInput()->getDataAt(4));
		$this->assertEquals(14.0, $pair->getInput()->getDataAt(5));
		$this->assertEquals(16.0, $pair->getInput()->getDataAt(6));
		$this->assertEquals(17.0, $pair->getInput()->getDataAt(7));
		$this->assertEquals(19.0, $pair->getInput()->getDataAt(8));
		$this->assertEquals(20.0, $pair->getInput()->getDataAt(9));
		$this->assertEquals(24.0, $pair->getIdeal()->getDataAt(0));

		$iterator->next();
		$this->assertFalse($iterator->valid());
	}

	public function testFormatTemporal() {
		$temporal = new TemporalMLDataSet(5, 1);
		$temporal->addDescription(new TemporalDataDescription(TemporalDataType::$DELTA_CHANGE, true, false));
		$temporal->addDescription(new TemporalDataDescription(TemporalDataType::$PERCENT_CHANGE, true, false));
		$temporal->addDescription(new TemporalDataDescription(TemporalDataType::$RAW, false, true));
		for ($i = 0; $i < 10; $i++) {
			$point = $temporal->createPoint($i);
			$point->setDataAt(0, 1+($i*3));
			$point->setDataAt(1, 2+($i*3));
			$point->setDataAt(2, 3+($i*3));
		}
		$temporal->generate();

		$this->assertEquals(10, $temporal->calculateActualSetSize());
		$this->assertEquals(10, $temporal->getInputNeuronCount());
		$this->assertEquals(1, $temporal->getOutputNeuronCount());

		$iterator = $temporal->getIterator();
		/** @var MLDataPair $pair */
		$pair = $iterator->current();

		$this->assertEquals(10, $pair->getInput()->size());
		$this->assertEquals(1, $pair->getIdeal()->size());
		$this->assertEquals(3.0, $pair->getInput()->getDataAt(0));
		$this->assertEquals(1.5, $pair->getInput()->getDataAt(1));
		$this->assertEquals(3.0, $pair->getInput()->getDataAt(2));
		$this->assertEquals(0.6, $pair->getInput()->getDataAt(3));
		$this->assertEquals(3.0, $pair->getInput()->getDataAt(4));
		$this->assertEquals(0.375, $pair->getInput()->getDataAt(5));
		$this->assertEquals(3.0, $pair->getInput()->getDataAt(6));
		$this->assertEquals(0.25, round($pair->getInput()->getDataAt(7)*4.0)/4.0);
		$this->assertEquals(3.0, $pair->getInput()->getDataAt(8));
		$this->assertEquals(0.25, round($pair->getInput()->getDataAt(9)*4.0)/4.0);
		$this->assertEquals(18.0, $pair->getIdeal()->getDataAt(0));
	}

	public function testActivationTemporal() {
		$temporal = new TemporalMLDataSet(5, 1);
		$temporal->addDescription(new TemporalDataDescription(TemporalDataType::$RAW, true, false, 0, 0, new ActivationTANH()));
		$temporal->addDescription(new TemporalDataDescription(TemporalDataType::$RAW, true, false, 0, 0, new ActivationTANH()));
		$temporal->addDescription(new TemporalDataDescription(TemporalDataType::$RAW, false, true, 0, 0, new ActivationTANH()));
		for ($i = 0; $i < 10; $i++) {
			$point = $temporal->createPoint($i);
			$point->setDataAt(0, 1+($i*3));
			$point->setDataAt(1, 2+($i*3));
			$point->setDataAt(2, 3+($i*3));
		}
		$temporal->generate();

		$this->assertEquals(10, $temporal->calculateActualSetSize());
		$this->assertEquals(10, $temporal->getInputNeuronCount());
		$this->assertEquals(1, $temporal->getOutputNeuronCount());

		$iterator = $temporal->getIterator();
		/** @var MLDataPair $pair */
		$pair = $iterator->current();

		$this->assertEquals(10, $pair->getInput()->size());
		$this->assertEquals(1, $pair->getIdeal()->size());
		$this->assertEquals(0.75, round($pair->getInput()->getDataAt(0)*4.0)/4.0);
		$this->assertEquals(1.0, round($pair->getInput()->getDataAt(1)*4.0)/4.0);
		$this->assertEquals(1.0, round($pair->getInput()->getDataAt(2)*4.0)/4.0);
		$this->assertEquals(1.0, round($pair->getInput()->getDataAt(3)*4.0)/4.0);
	}
}
