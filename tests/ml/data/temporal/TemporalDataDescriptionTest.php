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
namespace encog\test\ml\data\temporal;

use encog\engine\network\activation\ActivationSigmoid;
use encog\engine\network\activation\ActivationTANH;
use encog\ml\data\temporal\TemporalDataDescription;
use encog\ml\data\temporal\TemporalDataType;
use PHPUnit\Framework\TestCase;

class TemporalDataDescriptionTest extends TestCase {
	public function testDataDescription() {
		$desc = new TemporalDataDescription(TemporalDataType::$RAW, true, false);
		$this->assertEquals(TemporalDataType::$RAW, $desc->getType());
		$this->assertEquals(0, $desc->getLow());
		$this->assertEquals(0, $desc->getHigh());
		$this->assertEquals(0, $desc->getIndex());
		$this->assertTrue($desc->isInput());
		$this->assertFalse($desc->isPredict());
		$this->assertNull($desc->getActivation());
		$desc->setIndex(1);
		$this->assertEquals(1, $desc->getIndex());

		$activation = new ActivationTANH();
		$desc = new TemporalDataDescription(TemporalDataType::$PERCENT_CHANGE, false, true, -1, 1, $activation);
		$desc->setIndex(2);

		$this->assertEquals(TemporalDataType::$PERCENT_CHANGE, $desc->getType());
		$this->assertEquals($activation, $desc->getActivation());
		$this->assertEquals(-1, $desc->getLow());
		$this->assertEquals(1, $desc->getHigh());
		$this->assertEquals(2, $desc->getIndex());
		$this->assertFalse($desc->isInput());
		$this->assertTrue($desc->isPredict());
	}
}
