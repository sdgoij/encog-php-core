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
namespace encog\test\neural\networks;

use ArrayIterator;
use encog\engine\network\activation\ActivationLinear;
use encog\engine\network\activation\ActivationSigmoid;
use encog\ml\data\basic\BasicMLData;
use encog\ml\factory\MLMethodFactory;
use encog\ml\MLRegression;
use encog\neural\flat\FlatNetwork;
use encog\neural\networks\BasicNetwork;
use encog\neural\networks\layers\BasicLayer;
use encog\neural\networks\structure\NeuralStructure;
use encog\neural\networks\training\propagation\resilient\ResilientPropagation;
use encog\neural\NeuralNetworkError;
use encog\neural\pattern\ElmanPattern;
use encog\neural\pattern\JordanPattern;
use encog\util\benchmark\RandomTrainingFactory;
use encog\util\Random;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionObject;
use SplFixedArray;

class BasicNetworkTest extends TestCase {
	public function testToString() {
		$this->assertEquals("[BasicNetwork: Layers=3]", (string)XORUtil::createTrainedNetwork());
		$this->assertEquals("[BasicNetwork: Layers=3]", (string)XORUtil::createThreeLayerNetwork());
	}

	public function testAddLayer() {
		$network = new BasicNetwork();
		$network->addLayer(BasicLayer::create(2));
		$network->addLayer(BasicLayer::create(5));
		$network->addLayer(BasicLayer::create(1));

		$this->assertCount(3, $network->getStructure()->getLayers());

		foreach ($network->getStructure()->getLayers() as $layer) {
			$this->assertSame($network, $layer->getNetwork());
		}
	}

	public function testAddWeight() {
		$network = new BasicNetwork();

		$structure = $network->getStructure();
		$structure->addLayer(BasicLayer::create(1));
		$structure->addLayer(BasicLayer::create(1));
		$structure->finalizeStructure();

		$network->addWeight(0, 0, 0, 0.4);
		$network->addWeight(0, 0, 0, 0.6);

		$this->assertSame(1.0, $network->getWeight(0, 0 , 0));
		$this->expectException(NeuralNetworkError::class);
		$this->expectExceptionMessage("The specified layer is not connected to another layer: 1");
		$network->setWeight(1, 0, 0, 1.0);
	}

	public function testSetWeight() {
		$network = new BasicNetwork();

		$structure = $network->getStructure();
		$structure->addLayer(BasicLayer::create(1));
		$structure->addLayer(BasicLayer::create(1));
		$structure->finalizeStructure();

		$network->setWeight(0, 0, 0, 1.0);

		$this->assertSame(1.0, $network->getWeight(0, 0, 0));
		$this->expectException(NeuralNetworkError::class);
		$this->expectExceptionMessage("The specified layer is not connected to another layer: 1");
		$network->setWeight(1, 0, 0, 1.0);
	}

	public function testIsLayerBiased() {
		$network = new BasicNetwork();

		$structure = $network->getStructure();
		$structure->addLayer(BasicLayer::create(1, null, false));
		$structure->addLayer(BasicLayer::create(1, null, true));
		$structure->finalizeStructure();

		$this->assertFalse($network->isLayerBiased(0));
		$this->assertTrue($network->isLayerBiased(1));

		$this->expectException(NeuralNetworkError::class);
		$this->expectExceptionMessage("Must call finalizeStructure before using this network.");
		(new BasicNetwork())->isLayerBiased(0);
	}

	public function testSetBiasActivation() {
		$network = new BasicNetwork();

		$structure = $network->getStructure();
		$structure->addLayer(BasicLayer::create(1, null, false));
		$structure->addLayer(BasicLayer::create(1, null, true));

		$network->setBiasActivation(0.9);

		$this->assertSame(0.0, $structure->getLayers()[0]->getBiasActivation());
		$this->assertSame(0.9, $structure->getLayers()[1]->getBiasActivation());

		$structure->finalizeStructure();

		$network->setBiasActivation(1.0);
		$this->assertSame(1.0, $structure->getFlat()->getLayerOutput()[1]);
	}

	public function testSetLayerBiasActivation() {
		$network = new BasicNetwork();
		$structure = $network->getStructure();
		$structure->addLayer(BasicLayer::create(1, null, false));
		$structure->addLayer(BasicLayer::create(1, null, true));
		$structure->finalizeStructure();

		$network->setLayerBiasActivation(1, 0.8);

		$this->assertSame(0.8, $structure->getFlat()->getLayerOutput()[1]);
		$this->expectException(NeuralNetworkError::class);
		$this->expectExceptionMessage("Error, the specified layer does not have a bias: 0");
		$network->setLayerBiasActivation(0, 0.1);
	}

	public function testUpdateProperties() {
		/** @var NeuralStructure|MockObject $structure */
		$structure = $this->createMock(NeuralStructure::class);
		$structure->expects($this->once())->method("updateProperties");
		$this->createNetworkFromMock($structure)->updateProperties();
	}

	public function testClearContext() {
		/** @var NeuralStructure|MockObject $flat */
		$flat = $this->createMock(FlatNetwork::class);
		$flat->expects($this->once())->method("clearContext");

		/** @var NeuralStructure|MockObject $structure */
		$structure = $this->createMock(NeuralStructure::class);
		$structure->expects($this->exactly(2))
			->method("getFlat")
			->willReturn(null, $flat);

		$network = $this->createNetworkFromMock($structure);
		$network->clearContext();
		$network->clearContext();
	}

	public function testCalculateError() {
		$network = new BasicNetwork();
		$network->addLayer(BasicLayer::create(2));
		$network->addLayer(BasicLayer::create(3));
		$network->addLayer(BasicLayer::create(1));
		$network->getStructure()->finalizeStructure();

		$this->assertSame(0.25, $network->calculateError(XORUtil::createDataSet()));
	}

	public function testGetLayerCount() {
		$network = new BasicNetwork();
		$network->addLayer(BasicLayer::create(10));
		$network->addLayer(BasicLayer::create(25));
		$network->addLayer(BasicLayer::create(50));
		$network->addLayer(BasicLayer::create(50));
		$network->addLayer(BasicLayer::create(40));
		$network->addLayer(BasicLayer::create(10));
		$network->getStructure()->finalizeStructure();
		$this->assertEquals(6, $network->getLayerCount());

		$this->expectException(NeuralNetworkError::class);
		$this->expectExceptionMessage("Must call finalizeStructure before using this network.");
		(new BasicNetwork())->getLayerCount();
	}

	public function testGetLayerNeuronCount() {
		/** @var NeuralStructure|MockObject $flat */
		$flat = $this->createMock(FlatNetwork::class);
		$flat->expects($this->once())
			->method("getLayerFeedCounts")
			->willReturn(SplFixedArray::fromArray([2]));
		$flat->expects($this->once())
			->method("getLayerCounts")
			->willReturn(SplFixedArray::fromArray([1]));

		/** @var NeuralStructure|MockObject $structure */
		$structure = $this->createMock(NeuralStructure::class);
		$structure->expects($this->exactly(2))
			->method("requireFlat")
			->willReturn($flat);

		$this->assertEquals(2, $this->createNetworkFromMock($structure)->getLayerNeuronCount(0));
	}

	public function testGetLayerOutput() {
		$network = new BasicNetwork();
		$network->addLayer(BasicLayer::create(1));
		$network->addLayer(BasicLayer::create(2));
		$network->getStructure()->finalizeStructure();

		$this->assertSame(0.0, $network->getLayerOutput(0, 0));
		$this->assertSame(1.0, $network->getLayerOutput(0, 1));

		$this->assertSame(0.0, $network->getLayerOutput(1, 0));
		$this->assertSame(0.0, $network->getLayerOutput(1, 1));
		$this->assertSame(1.0, $network->getLayerOutput(1, 2));
		$this->assertSame(0.0, $network->getLayerOutput(1, 3));
		$this->assertSame(1.0, $network->getLayerOutput(1, 4));

		$this->expectException(NeuralNetworkError::class);
		$this->expectExceptionMessage("The layer index: 5 specifies an output index larger than the network has.");
		$network->getLayerOutput(0, 2);
	}

	public function testCalculateNeuronCount() {
		$network = new BasicNetwork();
		$network->addLayer(BasicLayer::create(2));
		$network->addLayer(BasicLayer::create(3));
		$network->addLayer(BasicLayer::create(1));

		$this->assertEquals(6, $network->calculateNeuronCount());
		$network->getStructure()->finalizeStructure();
		$network->reset(1);

		$this->assertEquals(0, $network->calculateNeuronCount());
	}

	public function testComputeArray() {
		$network = new BasicNetwork();
		$network->addLayer(BasicLayer::create(2));
		$network->addLayer(BasicLayer::create(3));
		$network->addLayer(BasicLayer::create(1));
		$network->getStructure()->finalizeStructure();

		$output = [[], [], [], []];

		$network->computeArray([0,0], $output[0]);
		$network->computeArray([0,1], $output[1]);
		$network->computeArray([1,0], $output[2]);
		$network->computeArray([1,1], $output[3]);

		$this->assertSame(0.5, $output[0][0]);
		$this->assertSame(0.5, $output[1][0]);
		$this->assertSame(0.5, $output[2][0]);
		$this->assertSame(0.5, $output[3][0]);
	}

	public function testGetFactoryType() {
		$this->assertEquals(MLMethodFactory::TYPE_FEEDFORWARD, (new BasicNetwork())->getFactoryType());
	}

	public function testGetFactoryArchitecture() {
		$network = new BasicNetwork();
		$network->addLayer(BasicLayer::create(2));
		$network->addLayer(BasicLayer::create(3));
		$network->addLayer(BasicLayer::create(1));
		$network->getStructure()->finalizeStructure();

		$this->assertEquals(
			"2:B->SIGMOID->3:B->SIGMOID->1:B",
			$network->getFactoryArchitecture()
		);
	}

	public function testEnableConnection() {
		$network = new BasicNetwork();
		$network->addLayer(BasicLayer::create(2));
		$network->addLayer(BasicLayer::create(3));
		$network->addLayer(BasicLayer::create(1));
		$network->getStructure()->finalizeStructure();

		$network->enableConnection(1, 0, 0, false);
		$network->enableConnection(1, 1, 0, false);

		$this->assertTrue($network->getStructure()->isConnectionLimited());
		$this->assertSame(0.0, $network->getStructure()->getFlat()->getWeights()[0]);
		$this->assertSame(0.0, $network->getStructure()->getFlat()->getWeights()[1]);

		$network->enableConnection(1, 0, 0, true);
		$network->enableConnection(1, 1, 0, true);

		$this->assertTrue($network->getStructure()->isConnectionLimited());
		$this->assertNotSame(0.0, $network->getStructure()->getFlat()->getWeights()[0]);
		$this->assertNotSame(0.0, $network->getStructure()->getFlat()->getWeights()[1]);
	}

	public function testWinner() {
		$network = new BasicNetwork();
		$network->addLayer(BasicLayer::create(2));
		$network->addLayer(BasicLayer::create(3));
		$network->addLayer(BasicLayer::create(1));
		$network->getStructure()->finalizeStructure();

		$this->assertEquals(0, $network->winner(new BasicMLData([0,0])));
		$this->assertEquals(0, $network->winner(new BasicMLData([0,1])));
		$this->assertEquals(0, $network->winner(new BasicMLData([1,0])));
		$this->assertEquals(0, $network->winner(new BasicMLData([1,1])));

		$this->assertEquals(
			$network->winner(new BasicMLData([0,0])),
			$network->classify(new BasicMLData([0,0]))
		);
		$this->assertEquals(
			$network->winner(new BasicMLData([0,1])),
			$network->classify(new BasicMLData([0,1]))
		);
		$this->assertEquals(
			$network->winner(new BasicMLData([1,0])),
			$network->classify(new BasicMLData([1,0]))
		);
		$this->assertEquals(
			$network->winner(new BasicMLData([1,1])),
			$network->classify(new BasicMLData([1,1]))
		);
	}

	public function testValidateNeuron() {
		/** @var NeuralStructure|MockObject $flat */
		$flat = $this->createMock(FlatNetwork::class);
		$flat->expects($this->exactly(4))->method("validateNeuron");

		/** @var NeuralStructure|MockObject $structure */
		$structure = $this->createMock(NeuralStructure::class);
		$structure->expects($this->exactly(4))
			->method("requireFlat")
			->willReturn($flat);

		$network = $this->createNetworkFromMock($structure);
		$network->validateNeuron(0, 0);
		$network->validateNeuron(0, 1);
		$network->validateNeuron(1, 0);
		$network->validateNeuron(1, 1);
	}

	public function testMaxIndex() {
		$this->assertEquals(1, BasicNetwork::maxIndex([1,2,0]));
		$this->assertEquals(1, BasicNetwork::maxIndex(new ArrayIterator([1,2,0])));
		$this->assertEquals(1, BasicNetwork::maxIndex(SplFixedArray::fromArray([1,2,0])));
		$this->expectException(InvalidArgumentException::class);
		BasicNetwork::maxIndex(1);
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
		// @phpstan-ignore method.alreadyNarrowedType
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
		// @phpstan-ignore method.alreadyNarrowedType
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

	private function createNetworkFromMock($mock): BasicNetwork {
		return new class($mock) extends BasicNetwork {
			public function __construct(MockObject $mock) {
				parent::__construct();
				$structure = (new ReflectionObject($this))
					->getParentClass()
					->getProperty("structure");
				$structure->setAccessible(true);
				$structure->setValue($this, $mock);
			}
		};
	}

	private function performElmanTest(int $input, int $hidden, int $ideal) {
		$pattern = new ElmanPattern();
		$pattern->setInputNeurons($input);
		$pattern->addHiddenLayer($hidden);
		$pattern->setOutputNeurons($ideal);

		/** @var BasicNetwork */
		$network = $pattern->generate();
		if (!$network instanceof BasicNetwork) {
			$this->fail("Expected instance of 'BasicNetwork'");
		}
		$this->assertInstanceOf(BasicNetwork::class, $network);

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

		/** @var BasicNetwork */
		$network = $pattern->generate();
		if (!$network instanceof BasicNetwork) {
			$this->fail("Expected instance of 'BasicNetwork'");
		}
		$this->assertInstanceOf(BasicNetwork::class, $network);

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
