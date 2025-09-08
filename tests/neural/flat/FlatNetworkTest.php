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
namespace encog\test\neural\flat;

use encog\EncogError;
use encog\engine\network\activation\ActivationLinear;
use encog\engine\network\activation\ActivationSigmoid;
use encog\neural\flat\FlatLayer;
use encog\neural\flat\FlatNetwork;
use encog\neural\NeuralNetworkError;
use encog\test\neural\networks\XORUtil;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SplFixedArray;

class FlatNetworkTest extends TestCase {
	public function testCreateFromArray() {
		$flat = FlatNetwork::createFromArray([
			new FlatLayer(null, 2, 1.0),
			new FlatLayer(new ActivationSigmoid(), 1, 0.0)
		]);
		$this->assertEquals(0.0, $flat->getConnectionLimit());
		$this->assertEquals(2, count($flat->getLayerCounts()));
		$this->assertEquals(4, $flat->getNeuronCount());
		$this->assertFalse($flat->isLimited());

		$flat = FlatNetwork::createFromArray([
			new FlatLayer(null, 2, 1.0, 1.0),
			new FlatLayer(new ActivationLinear(), 1, 0.0, 0.5),
			new FlatLayer(null, 2, 1.0, 0.25),
		], true);
		$this->assertEquals(3, count($flat->getLayerCounts()));
		$this->assertEquals(7, $flat->getNeuronCount());
		$this->assertEquals(
			[0.25, 0.5, 1.0],
			$flat->getLayerDropoutRates()->toArray()
		);

		$this->expectExceptionMessage("\$layers should at least contain a input and output layer");
		$this->expectException(InvalidArgumentException::class);
		FlatNetwork::createFromArray([
			new FlatLayer(null, 2, 1.0),
		]);
	}

	public function testCreateFlat() {
		$flat = new FlatNetwork(2, 0, 0, 1);
		$this->assertEquals(0.0, $flat->getConnectionLimit());
		$this->assertEquals(2, count($flat->getLayerCounts()));
		$this->assertEquals(4, $flat->getNeuronCount());
		$this->assertFalse($flat->isLimited());

		$flat = new FlatNetwork(2, 3, 0, 1);
		$this->assertEquals(new FlatNetwork(2, 0, 3, 1), $flat);
		$this->assertEquals(0.0, $flat->getConnectionLimit());
		$this->assertEquals(3, count($flat->getLayerCounts()));
		$this->assertEquals(8, $flat->getNeuronCount());
		$this->assertFalse($flat->isLimited());

		$flat = new FlatNetwork(2, 3, 3, 1);
		$this->assertEquals(0.0, $flat->getConnectionLimit());
		$this->assertEquals(4, count($flat->getLayerCounts()));
		$this->assertEquals(12, $flat->getNeuronCount());
		$this->assertFalse($flat->isLimited());
	}

	public function testCalculateError() {
		$this->assertSame(0.25, (new FlatNetwork(2, 3, 0, 1, false))->calculateError(XORUtil::createDataSet()));
	}

	public function testValidateOK() {
		$flat = new FlatNetwork(2, 3, 0, 1);
		$this->assertEquals($flat, $flat->validateNeuron(0, 0));
		$this->assertEquals($flat, $flat->validateNeuron(0, 1));
		$this->assertEquals($flat, $flat->validateNeuron(0, 2));
		$this->assertEquals($flat, $flat->validateNeuron(1, 0));
		$this->assertEquals($flat, $flat->validateNeuron(1, 1));
		$this->assertEquals($flat, $flat->validateNeuron(1, 2));
		$this->assertEquals($flat, $flat->validateNeuron(1, 3));
		$this->assertEquals($flat, $flat->validateNeuron(2, 0));
	}

	public function testValidateInvalidLayer() {
		$this->expectExceptionMessage("Invalid layer count: 3");
		$this->expectException(NeuralNetworkError::class);
		(new FlatNetwork(2, 3, 0, 1))->validateNeuron(3, 4);
	}

	public function testValidateInvalidNeuron() {
		$this->expectExceptionMessage("Invalid neuron number: 3");
		$this->expectException(NeuralNetworkError::class);
		(new FlatNetwork(2, 3, 0, 1))->validateNeuron(2, 3);
	}

	public function testEncodeNetwork() {
		$this->assertEquals(array_fill(0, 13, 0), (new FlatNetwork(2, 3, 0, 1))->encodeNetwork());
		$this->assertEquals(array_fill(0, 245, 0), (new FlatNetwork(10, 15, 0, 5))->encodeNetwork());
		$this->assertEquals(13, (new FlatNetwork(2, 3, 0, 1))->getEncodeLength());
	}

	public function testDecodeNetwork() {
		$flat = new FlatNetwork(2, 3, 0, 1);
		$flat->decodeNetwork(range(0, 12));
		$this->assertEquals(range(0, 12), $flat->getWeights()->toArray());
		$this->expectExceptionMessage("Incompatible weight sizes, can't assign length=15 to length=13");
		$this->expectException(EncogError::class);
		$flat->decodeNetwork(range(0, 14));
	}

	public function testLayerNeuronCount() {
		$flat = new FlatNetwork(2, 3, 0, 1);

		$this->assertEquals(2, $flat->getLayerNeuronCount(0));
		$this->assertEquals(3, $flat->getLayerNeuronCount(1));
		$this->assertEquals(1, $flat->getLayerNeuronCount(2));

		$flat->init([
			new FlatLayer(null, 2, 1.0),
			new FlatLayer(null, 5, 1.0),
			new FlatLayer(null, 15, 1.0),
			new FlatLayer(null, 1, 0.0),
		], false);

		$this->assertEquals(2, $flat->getLayerNeuronCount(0));
		$this->assertEquals(5, $flat->getLayerNeuronCount(1));
		$this->assertEquals(15, $flat->getLayerNeuronCount(2));
		$this->assertEquals(1, $flat->getLayerNeuronCount(3));
	}

	public function testRandomize() {
		$flat = new FlatNetwork(2, 3, 0, 1);
		$flat->randomize();

		foreach ($flat->getWeights() as $weight) {
			$this->assertGreaterThanOrEqual(-1, $weight);
			$this->assertLessThanOrEqual(1, $weight);
		}

		$flat->randomize(3.2, 3.14);
		foreach ($flat->getWeights() as $weight) {
			$this->assertGreaterThanOrEqual(3.14, $weight);
			$this->assertLessThanOrEqual(3.2, $weight);
		}
	}

	public function testCompute() {
		$flat = new FlatNetwork(2, 3, 0, 1);
		$output = new SplFixedArray($flat->getOutputCount());

		$flat->compute([M_PI,M_PI], $output);
		$this->assertEquals([0.5], $output->toArray());
	}

	public function testGetWeight() {
		$flat = new FlatNetwork(2, 3, 0, 1);

		$this->assertEmpty($flat->getWeight(0, 0, 0));
		$this->assertEmpty($flat->getWeight(0, 0, 1));
		$this->assertEmpty($flat->getWeight(0, 0, 2));

		$this->assertEmpty($flat->getWeight(0, 1, 0));
		$this->assertEmpty($flat->getWeight(0, 1, 1));
		$this->assertEmpty($flat->getWeight(0, 1, 2));

		$this->assertEmpty($flat->getWeight(1, 0, 0));
		$this->assertEmpty($flat->getWeight(1, 1, 0));
		$this->assertEmpty($flat->getWeight(1, 2, 0));
	}

	public function testValidateNeuron() {
		$flat = new FlatNetwork(2, 3, 0, 1);
		$test = function (int $layer, int $neuron) use ($flat) {
			return function () use ($flat, $layer, $neuron) {
				return $flat->validateNeuron($layer, $neuron);
			};
		};

		$this->expectNeuralNetworkError($test(-1, 0), "Invalid layer count: -1");
		$this->expectNeuralNetworkError($test(3, 0), "Invalid layer count: 3");
		$this->expectNeuralNetworkError($test(0, -1), "Invalid neuron number: -1");
		$this->expectNeuralNetworkError($test(0, 3), "Invalid neuron number: 3");
		$this->expectNeuralNetworkError($test(1, -42), "Invalid neuron number: -42");
		$this->expectNeuralNetworkError($test(1, 4), "Invalid neuron number: 4");
		$this->expectNeuralNetworkError($test(2, -5), "Invalid neuron number: -5");
		$this->expectNeuralNetworkError($test(2, 1), "Invalid neuron number: 1");

		try {
			$this->assertSame($flat, $test(0, 0)());
			$this->assertSame($flat, $test(0, 1)());
			$this->assertSame($flat, $test(0, 2)());
			$this->assertSame($flat, $test(1, 0)());
			$this->assertSame($flat, $test(1, 1)());
			$this->assertSame($flat, $test(1, 2)());
			$this->assertSame($flat, $test(1, 3)());
			$this->assertSame($flat, $test(2, 0)());
		} catch (\Exception $e) {
			$this->fail("unexpected exception: {$e->getMessage()}");
		}
	}

	private function expectNeuralNetworkError(callable $fn, string $message) {
		try {
			$fn();
			$this->fail("Failed asserting that exception of type \"".NeuralNetworkError::class."\" is thrown.");
		} catch (NeuralNetworkError $e) {
			$this->assertEquals($message, $e->getMessage(),
				"Failed asserting that exception message '{$e->getMessage()}' contains '$message."
			);
		}
	}

	public function testConnectionLimit() {
		$network = new FlatNetwork();
		$network->setConnectionLimit(0.1);
		$network->setLimited(true);

		$this->assertTrue($network->isLimited());
		$this->assertEquals(0.1, $network->getConnectionLimit());

		$network->clearConnectionLimit();

		$this->assertFalse($network->isLimited());
		$this->assertEquals(0.0, $network->getConnectionLimit());
	}

	public function testSetActivationFunctions() {
		$network = new FlatNetwork();
		$network->setActivationFunctions(
			new ActivationLinear(),
			new ActivationSigmoid()
		);
		$this->assertCount(2, $network->getActivationFunctions());
	}

	public function testGetSetProperties() {
		$expected = SplFixedArray::fromArray([1,2,3]);

		$network = new FlatNetwork();
		$network->setInputCount(2);
		$network->setOutputCount(1);
		$network->setBiasActivation($expected);
		$network->setWeightIndex($expected);
		$network->setWeights($expected);
		$network->setContextTargetOffset($expected);
		$network->setContextTargetSize($expected);
		$network->setLayerContextCount($expected);
		$network->setLayerCounts($expected);
		$network->setLayerDropoutRates($expected);
		$network->setLayerFeedCounts($expected);
		$network->setLayerIndex($expected);
		$network->setLayerOutput($expected);
		$network->setLayerSums($expected);
		$network->setBeginTraining(8);
		$network->setEndTraining(12);
		$network->setHasContext(true);

		$this->assertSame(2, $network->getInputCount());
		$this->assertSame(1, $network->getOutputCount());
		$this->assertSame($expected, $network->getBiasActivation());
		$this->assertSame($expected, $network->getWeightIndex());
		$this->assertSame($expected, $network->getWeights());
		$this->assertSame($expected, $network->getContextTargetOffset());
		$this->assertSame($expected, $network->getContextTargetSize());
		$this->assertSame($expected, $network->getLayerContextCount());
		$this->assertSame($expected, $network->getLayerCounts());
		$this->assertSame($expected, $network->getLayerDropoutRates());
		$this->assertSame($expected, $network->getLayerFeedCounts());
		$this->assertSame($expected, $network->getLayerIndex());
		$this->assertSame($expected, $network->getLayerOutput());
		$this->assertSame($expected, $network->getLayerSums());
		$this->assertSame(8, $network->getBeginTraining());
		$this->assertSame(12, $network->getEndTraining());
		$this->assertTrue($network->getHasContext());
	}
}
