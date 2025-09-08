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
namespace encog\test\neural\networks\layers;

use encog\engine\network\activation\ActivationSigmoid;
use encog\neural\networks\layers\BasicLayer;
use encog\test\neural\networks\XORUtil;
use PHPUnit\Framework\TestCase;

class BasicLayerTest extends TestCase {
	public function testCreateBasicLayer() {
		$layer = BasicLayer::create(2);
		$this->assertEquals(new ActivationSigmoid(), $layer->getActivationFunction());
		$this->assertEquals(2, $layer->getNeuronCount());
		$this->assertNull($layer->getNetwork());
		$this->assertTrue($layer->hasBias());
		$this->assertSame(1.0, $layer->getBiasActivation());

		$network = XORUtil::createThreeLayerNetwork();
		$layer->setNetwork($network);

		$this->assertSame($network, $layer->getNetwork());
	}
}
