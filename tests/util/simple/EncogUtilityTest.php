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
namespace encog\test\util\simple;

use encog\neural\networks\BasicNetwork;
use encog\test\util\PrivateConstructorTest;
use encog\util\simple\EncogUtility;
use PHPUnit\Framework\TestCase;

class EncogUtilityTest extends TestCase {
	use PrivateConstructorTest;

	public function testSimpleFeedForward() {
		/** @var BasicNetwork $network */
		$network = EncogUtility::simpleFeedForward(2, 3, 4, 1, false);
		$this->assertInstanceOf(BasicNetwork::class, $network);
		$this->assertEquals(2, $network->getInputCount());
		$this->assertEquals(4, $network->getLayerCount());
		$this->assertEquals(1, $network->getOutputCount());
	}

	protected function getSubjectClassName(): string {
		return EncogUtility::class;
	}
}
