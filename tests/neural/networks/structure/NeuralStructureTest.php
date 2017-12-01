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
namespace encog\test\neural\networks\structure;

use encog\neural\flat\FlatLayer;
use encog\neural\flat\FlatNetwork;
use encog\neural\networks\BasicNetwork;
use encog\neural\networks\layers\BasicLayer;
use encog\neural\networks\structure\NeuralStructure;
use encog\neural\NeuralNetworkError;
use encog\test\neural\networks\XORUtil;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SplFixedArray;

class NeuralStructureTest extends TestCase {
	public function testCalculateSize() {
		$this->assertEquals(13, (new NeuralStructure(XORUtil::createThreeLayerNetwork()))->calculateSize());
	}

	public function testEnforceLimit() {
		$network = new BasicNetwork();
		$network->setProperty(BasicNetwork::TAG_LIMIT, 1.0);

		/** @var FlatNetwork|MockObject $flat */
		$flat = $this->createMock(FlatNetwork::class);
		$flat->expects($this->once())
			->method("getWeights")
			->willReturn(SplFixedArray::fromArray([-1.0, 0.0, 0.5, 1.0, 1.1]));
		$flat->expects($this->once())
			->method("setWeights")
			->with(SplFixedArray::fromArray([-1.0, 0.0, 0.0, 1.0, 1.1]));

		$structure = new NeuralStructure($network);
		$structure->setFlat($flat);
		$structure->finalizeLimit();
	}

	public function testFinalizeLimit() {
		{
			$structure = new NeuralStructure(XORUtil::createTrainedNetwork());
			$structure->finalizeLimit();

			$this->assertFalse($structure->isConnectionLimited());
			$this->assertSame(0.0, $structure->getConnectionLimit());
		}
		{
			$network = XORUtil::createTrainedNetwork();
			$network->setProperty(BasicNetwork::TAG_LIMIT, 3.2);

			/** @var FlatNetwork|MockObject $flat */
			$flat = $this->createMock(FlatNetwork::class);
			$flat->expects($this->once())
				->method("getWeights")
				->willReturn(SplFixedArray::fromArray([1,2,3]));

			$structure = new NeuralStructure($network);
			$structure->setFlat($flat);
			$structure->finalizeLimit();

			$this->assertTrue($structure->isConnectionLimited());
			$this->assertSame(3.2, $structure->getConnectionLimit());
		}
	}

	public function testFinalizeStructure() {
		$structure = new NeuralStructure(new BasicNetwork());
		$this->assertCount(0, $structure->getLayers());
		$structure->getLayers()[] = new FlatLayer(null, 2);
		$structure->addLayer(BasicLayer::create(3));
		$this->assertCount(2, $structure->getLayers());
		$structure->addLayer(BasicLayer::create(1));
		$structure->finalizeStructure();
		$this->assertCount(0, $structure->getLayers());
		$this->assertNotNull($structure->getNetwork());

		$this->expectException(NeuralNetworkError::class);
		$this->expectExceptionMessage("There must be at least two layers before the structure is finalized.");
		(new NeuralStructure(new BasicNetwork()))->finalizeStructure();
	}

	public function testRequireFlat() {
		$structure = new NeuralStructure(new BasicNetwork());
		$structure->setFlat(new FlatNetwork());
		$this->assertNotNull($structure->requireFlat());
		$this->expectException(NeuralNetworkError::class);
		$this->expectExceptionMessage("Must call finalizeStructure before using this network.");
		(new NeuralStructure(new BasicNetwork()))->requireFlat();
	}

	public function testUpdateProperties() {
		$network = new BasicNetwork();
		$structure = new NeuralStructure($network);

		$this->assertFalse($structure->isConnectionLimited());
		$this->assertSame(0.0, $structure->getConnectionLimit());
		$structure->updateProperties();

		$this->assertFalse($structure->isConnectionLimited());
		$this->assertSame(0.0, $structure->getConnectionLimit());

		$network->setProperty(BasicNetwork::TAG_LIMIT, 0.999);

		/** @var FlatNetwork|MockObject $flat */
		$flat = $this->createMock(FlatNetwork::class);
		$flat->expects($this->once())->method("setConnectionLimit");

		$structure->setFlat($flat);
		$structure->updateProperties();

		$this->assertTrue($structure->isConnectionLimited());
		$this->assertSame(0.999, $structure->getConnectionLimit());
	}
}
