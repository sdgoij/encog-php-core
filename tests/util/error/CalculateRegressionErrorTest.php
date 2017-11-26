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
namespace encog\test\util\error;

use encog\neural\networks\BasicNetwork;
use encog\neural\networks\layers\BasicLayer;
use encog\test\neural\networks\XORUtil;
use encog\util\error\CalculateRegressionError;
use PHPUnit\Framework\TestCase;

class CalculateRegressionErrorTest extends TestCase {
	public function testCalculateError() {
		$network = new BasicNetwork();
		$network->addLayer(BasicLayer::create(2));
		$network->addLayer(BasicLayer::create(3));
		$network->addLayer(BasicLayer::create(1));
		$network->getStructure()->finalizeStructure();

		$this->assertSame(0.25, CalculateRegressionError::calculateError($network, XORUtil::createDataSet()));
	}
}
