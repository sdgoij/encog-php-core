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
namespace encog\test\neural\flat;

use encog\engine\network\activation\ActivationLinear;
use encog\engine\network\activation\ActivationSigmoid;
use encog\engine\network\activation\ActivationTANH;
use encog\neural\flat\FlatLayer;
use PHPUnit_Framework_TestCase as TestCase;

class FlatLayerTest extends TestCase {
	public function testCreateFlatLayer() {
		$layer = new FlatLayer();
		$this->assertEquals(new ActivationLinear(), $layer->getActivation());
		$this->assertEquals(1.0, $layer->getBiasActivation());
		$this->assertEquals(0.0, $layer->getDropoutRate());
		$this->assertEquals(0, $layer->getContextCount());
		$this->assertEquals(null, $layer->getContextFedBy());
		$this->assertEquals(0, $layer->getCount());

		$layer = new FlatLayer(new ActivationTANH(), 3, 0.0, 0.5);
		$layer->setContextFedBy($layer);
		$this->assertEquals(new ActivationTANH(), $layer->getActivation());
		$this->assertEquals(0.0, $layer->getBiasActivation());
		$this->assertEquals(0.5, $layer->getDropoutRate());
		$this->assertEquals(3, $layer->getContextCount());
		$this->assertEquals($layer, $layer->getContextFedBy());
		$this->assertEquals(3, $layer->getCount());
	}

	public function testActivation() {
		$activation = new ActivationSigmoid();
		$layer = new FlatLayer();
		$layer->setActivation($activation);
		$this->assertEquals($activation, $layer->getActivation());
	}

	public function testDropoutRate() {
		$layer = new FlatLayer();
		$this->assertEquals(0.0, $layer->getDropoutRate());
		$layer->setDropoutRate(0.3);
		$this->assertEquals(0.3, $layer->getDropoutRate());
	}

	public function testToString() {
		$expect[] = sprintf("[%s:count=0,bias=1.000000]", FlatLayer::class);
		$expect[] = sprintf("[%s:count=3,bias=1.000000]", FlatLayer::class);
		$expect[] = sprintf("[%s:count=3,bias=1.000000,contextFed=itself]", FlatLayer::class);
		$expect[] = sprintf("[%s:count=2,bias=false,contextFed={$expect[2]}]", FlatLayer::class);

		$layer1 = new FlatLayer();
		$layer2 = new FlatLayer(null, 3, 1.0);
		$layer3 = new FlatLayer(null, 3, 1.0);
		$layer3->setContextFedBy($layer3);
		$layer4 = new FlatLayer(null, 2, 0.0);
		$layer4->setContextFedBy($layer3);

		$this->assertEquals($expect[0], (string)$layer1);
		$this->assertEquals($expect[1], (string)$layer2);
		$this->assertEquals($expect[2], (string)$layer3);
		$this->assertEquals($expect[3], (string)$layer4);
	}

	public function testTotalCount() {
		$this->assertEquals(1, (new FlatLayer())->getTotalCount());
		$this->assertEquals(0, (new FlatLayer(null, 0, 0.0))->getTotalCount());
		$this->assertEquals(3, (new FlatLayer(null, 2, 1.0))->getTotalCount());

		$layer = new FlatLayer(null, 2, 1.0);
		$layer->setContextFedBy(new FlatLayer(null, 3, 1.0));
		$this->assertEquals(6, $layer->getTotalCount());
	}
}
