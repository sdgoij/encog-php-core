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
namespace encog\test\ml\data\cross;

use encog\mathutil\randomize\generate\BasicGenerateRandom;
use encog\mathutil\randomize\generate\LinearCongruentialRandom;
use encog\ml\data\cross\KFoldCrossValidation;
use encog\ml\data\versatile\MatrixMLDataSet;
use PHPUnit_Framework_TestCase as TestCase;

class KFoldCrossValidationTest extends TestCase {
	public function testCreateKFold() {
		$KFold = new KFoldCrossValidation($this->createDataSet(), 2);
		$this->assertInstanceOf(BasicGenerateRandom::class, $KFold->getRandom());
		$this->assertEquals($this->createDataSet(), $KFold->getDataSet());
		$this->assertEquals([], $KFold->getFolds());
		$this->assertEquals(2, $KFold->getK());

		$random = new LinearCongruentialRandom();
		$KFold->setRandom($random);

		$this->assertTrue($random === $KFold->getRandom());
		$this->assertFalse($KFold === clone $KFold);
	}

	public function testProcessKFold() {
		$KFold = new KFoldCrossValidation($this->createDataSet(), 2);
		$KFold->process(false);

		$this->assertEquals($KFold->getK(), count($KFold->getFolds()));
	}

	public function testShuffleKFold() {
		$KFold = new KFoldCrossValidation($this->createDataSet(), 2);
		$KFold->process(true);

		$this->assertEquals($KFold->getK(), count($KFold->getFolds()));

		$first = $KFold->getFolds();
		$KFold->process(true);

		$this->assertEquals($KFold->getK(), count($KFold->getFolds()));
		$this->assertFalse($first === $KFold->getFolds());
	}

	private function createDataSet(): MatrixMLDataSet {
		return MatrixMLDataSet::createFromArray(
			[[0,1],[2,3],[4,5],[6,7],[8,9]], 1, 1
		);
	}
}
