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
namespace encog\test\util\identity;

use encog\util\identity\BasicGenerateID;
use PHPUnit\Framework\TestCase;

class BasicGenerateIDTest extends TestCase {
	public function testGetCurrentId() {
		$this->assertEquals(1, (new BasicGenerateID())->getCurrentID());
	}

	public function testSetCurrentId() {
		$sequence = new BasicGenerateID();
		$sequence->setCurrentID(42);

		$this->assertEquals(42, $sequence->getCurrentID());
	}

	public function testGenerateId() {
		$sequence = new BasicGenerateID();

		for ($i = 1; $i <= 100; $i++) {
			$this->assertEquals($i, $sequence->generate());
		}
	}
}
