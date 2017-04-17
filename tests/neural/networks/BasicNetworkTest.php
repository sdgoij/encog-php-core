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
namespace encog\test\neural\networks;

use encog\engine\network\activation\ActivationLinear;
use encog\engine\network\activation\ActivationSigmoid;
use encog\ml\data\basic\BasicMLData;
use encog\ml\MLRegression;
use encog\neural\networks\BasicNetwork;
use encog\neural\networks\layers\BasicLayer;
use encog\neural\networks\training\propagation\resilient\ResilientPropagation;
use encog\neural\pattern\ElmanPattern;
use encog\neural\pattern\JordanPattern;
use encog\util\benchmark\RandomTrainingFactory;
use encog\util\Random;
use PHPUnit_Framework_TestCase as TestCase;

class BasicNetworkTest extends TestCase {
	public function testToString() {
		$this->assertEquals("[BasicNetwork: Layers=3]", (string)XORUtil::createTrainedNetwork());
		$this->assertEquals("[BasicNetwork: Layers=3]", (string)XORUtil::createThreeLayerNetwork());
	}

	public function testTrainedXOR() {
		$network = XORUtil::createTrainedNetwork();
		$dataset = XORUtil::createDataSet();

		$this->assertLessThan(0.000001, $network->calculateError($dataset));
		$this->validateXOR($network, 1000);
	}

	public function testTrainXOR() {
		$network = XORUtil::createUnTrainedNetwork();
		$dataset = XORUtil::createDataSet();
		$trainer = new ResilientPropagation($network, $dataset);

		$this->assertGreaterThan(0.3, $network->calculateError($dataset));
		$this->assertEquals(0.0, $trainer->getError());

		do { $trainer->iteration(); } while ($trainer->getError() > 0.01);

		$this->assertEquals(66, $trainer->getIteration());
		$this->validateXOR($network, 1000);
	}

	public function testActivationFunctions() {
		$activations = XORUtil::createTrainedNetwork()->getFlat()->getActivationFunctions();
		$this->assertInstanceOf(ActivationSigmoid::class, $activations[0]);
		$this->assertInstanceOf(ActivationSigmoid::class, $activations[1]);
		$this->assertInstanceOf(ActivationLinear::class, $activations[2]);
	}

	public function testWeightAccess() {
		$network = XORUtil::createTrainedNetwork();
		$weights = $network->getFlat()->getWeights();
		$this->assertEquals($weights[5], $network->getWeight(0,0,0));
		$this->assertEquals($weights[8], $network->getWeight(0,0,1));
		$this->assertEquals($weights[11], $network->getWeight(0,0,2));
		$this->assertEquals($weights[14], $network->getWeight(0,0,3));
		$this->assertEquals($weights[6], $network->getWeight(0,1,0));
		$this->assertEquals($weights[9], $network->getWeight(0,1,1));
		$this->assertEquals($weights[12], $network->getWeight(0,1,2));
		$this->assertEquals($weights[15], $network->getWeight(0,1,3));
		$this->assertEquals($weights[7], $network->getWeight(0,2,0));
		$this->assertEquals($weights[10], $network->getWeight(0,2,1));
		$this->assertEquals($weights[13], $network->getWeight(0,2,2));
		$this->assertEquals($weights[16], $network->getWeight(0,2,3));
		$this->assertEquals($weights[0], $network->getWeight(1,0,0));
		$this->assertEquals($weights[1], $network->getWeight(1,1,0));
		$this->assertEquals($weights[2], $network->getWeight(1,2,0));
		$this->assertEquals($weights[3], $network->getWeight(1,3,0));
		$this->assertEquals($weights[4], $network->getWeight(1,4,0));
	}

	public function testLayerOutput() {
		$layer1 = BasicLayer::create(2, null, true);
		$layer2 = BasicLayer::create(4, null, true);

		$layer1->setBiasActivation(0.5);
		$layer2->setBiasActivation(-1);

		$network = new BasicNetwork();
		$network->addLayer($layer1);
		$network->addLayer($layer2);
		$network->addLayer(BasicLayer::create(1, null, false));
		$network->getStructure()->finalizeStructure();
		$network->reset();

		$flat = $network->getFlat();
		$this->assertNotNull($flat);

		$output = $flat->getLayerOutput();
		$this->assertEquals(-1.0, $output[5]);
		$this->assertEquals(0.5, $output[8]);
	}

	public function testLayerOutputPostFinalize() {
		$network = new BasicNetwork();
		$network->addLayer(BasicLayer::create(2, null, true));
		$network->addLayer(BasicLayer::create(4, null, true));
		$network->addLayer(BasicLayer::create(1, null, false));
		$network->getStructure()->finalizeStructure();
		$network->reset();

		$network->setLayerBiasActivation(0, 0.5);
		$network->setLayerBiasActivation(1, -1);

		$flat = $network->getFlat();
		$this->assertNotNull($flat);

		$output = $flat->getLayerOutput();
		$this->assertEquals(-1.0, $output[5]);
		$this->assertEquals(0.5, $output[8]);
	}

	public function testElmanRNN() {
		$this->performElmanTest(1, 2, 1);
		$this->performElmanTest(1, 5, 1);
		$this->performElmanTest(1, 25, 1);
		$this->performElmanTest(2, 2, 2);
		$this->performElmanTest(8, 2, 8);
	}

	public function testJordanRNN() {
		$this->performJordanTest(1, 2, 1);
		$this->performJordanTest(1, 5, 1);
		$this->performJordanTest(1, 25, 1);
		$this->performJordanTest(2, 2, 2);
		$this->performJordanTest(8, 2, 8);
	}

	private function performElmanTest(int $input, int $hidden, int $ideal) {
		$pattern = new ElmanPattern();
		$pattern->setInputNeurons($input);
		$pattern->addHiddenLayer($hidden);
		$pattern->setOutputNeurons($ideal);

		$network = $pattern->generate();
		if (!$network instanceof BasicNetwork) {
			$this->fail("Expected instance of 'BasicNetwork'");
		}
		$training = RandomTrainingFactory::generate(1000, 5, $network->getInputCount(), $network->getOutputCount(), -1, 1);
		$trainer = new ResilientPropagation($network, $training);
		$trainer->iteration();
		$trainer->iteration();
	}

	private function performJordanTest(int $input, int $hidden, int $ideal) {
		$pattern = new JordanPattern();
		$pattern->setInputNeurons($input);
		$pattern->addHiddenLayer($hidden);
		$pattern->setOutputNeurons($ideal);

		$network = $pattern->generate();
		if (!$network instanceof BasicNetwork) {
			$this->fail("Expected instance of 'BasicNetwork'");
		}
		$training = RandomTrainingFactory::generate(1000, 5, $network->getInputCount(), $network->getOutputCount(), -1, 1);
		$trainer = new ResilientPropagation($network, $training);
		$trainer->iteration();
		$trainer->iteration();
	}

	private function validateXOR(MLRegression $method, int $iterations) {
		$inputs = new BasicMLData(2);
		$random = new Random();
		for ($i = 0; $i < $iterations; $i++) {
			$inputs->setDataAt(0, $random->nextInt(2));
			$inputs->setDataAt(1, $random->nextInt(2));
			$this->assertEquals(
				$inputs->getDataAt(0) ^ $inputs->getDataAt(1),
				$method->compute($inputs)->getDataAt(0) > 0.5 ? 1 : 0
			);
		}
	}
}
