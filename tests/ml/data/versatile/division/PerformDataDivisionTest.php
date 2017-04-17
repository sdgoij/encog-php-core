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
namespace encog\test\ml\data\versatile\division;

use encog\mathutil\randomize\generate\LinearCongruentialRandom;
use encog\ml\data\versatile\division\DataDivision;
use encog\ml\data\versatile\division\PerformDataDivision;
use encog\ml\data\versatile\MatrixMLDataSet;
use PHPUnit_Framework_TestCase as TestCase;

class PerformDataDivisionTest extends TestCase {
	public function testPerformDivision() {
		$dataset = MatrixMLDataSet::createFromArray([[1,2],[3,4],[5,6],[7,8]], 1, 1);
		$divider = new PerformDataDivision(false, new LinearCongruentialRandom(1));

		/** @var DataDivision[] $divisions */
		$divisions[] = new DataDivision(0.5);
		$divisions[] = new DataDivision(0.5);

		$this->assertInstanceOf(LinearCongruentialRandom::class, $divider->getRandom());
		$this->assertFalse($divider->isShuffle());

		$divider->perform($divisions, $dataset, 1, 1);

		$this->assertEquals(2, $divisions[0]->getCount());
		$this->assertEquals(2, $divisions[1]->getCount());

		$div = $divisions[0]->getDataSet();
		$this->assertEquals(1, $div->get(0)->getInputArray()[0]);
		$this->assertEquals(2, $div->get(0)->getIdealArray()[0]);
		$this->assertEquals(3, $div->get(1)->getInputArray()[0]);
		$this->assertEquals(4, $div->get(1)->getIdealArray()[0]);

		$div = $divisions[1]->getDataSet();
		$this->assertEquals(5, $div->get(0)->getInputArray()[0]);
		$this->assertEquals(6, $div->get(0)->getIdealArray()[0]);
		$this->assertEquals(7, $div->get(1)->getInputArray()[0]);
		$this->assertEquals(8, $div->get(1)->getIdealArray()[0]);
	}
}
