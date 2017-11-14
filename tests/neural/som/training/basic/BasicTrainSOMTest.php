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
namespace encog\test\neural\som\training\basic;

use encog\mathutil\matrices\Matrix;
use encog\ml\data\basic\BasicMLData;
use encog\ml\data\basic\BasicMLDataSet;
use encog\neural\NeuralNetworkError;
use encog\neural\som\SOM;
use encog\neural\som\training\basic\BasicTrainSOM;
use encog\neural\som\training\basic\neighborhood\Bubble;
use encog\neural\som\training\basic\neighborhood\Single;
use PHPUnit\Framework\TestCase;

class BasicTrainSOMTest extends TestCase {
	public function testTrainInputPattern() {
		$som = new SOM(2, 2);
		$trainer = new BasicTrainSOM($som, 0.01, null, new Single());
		$trainer->trainInputPattern(new BasicMLData([1,2]));

		$this->assertSame(0.01, $som->getWeights()->get(0,0));
		$this->assertSame(0.02, $som->getWeights()->get(0,1));
		$this->assertSame(0.0, $som->getWeights()->get(1,0));
		$this->assertSame(0.0, $som->getWeights()->get(1,1));
	}

	public function testTrainingIteration() {
		$som = new SOM(4, 2);
		$trainer = new BasicTrainSOM($som, 0.7, new BasicMLDataSet([
			[-1.0, -1.0, 1.0, 1.0],
			[1.0, 1.0, -1.0, -1.0],
		]), new Single());
		$trainer->iteration();

		$this->assertSame(-0.7, $som->getWeights()->get(0, 0));
		$this->assertSame(-0.7, $som->getWeights()->get(0, 1));
		$this->assertSame(0.7, $som->getWeights()->get(0, 2));
		$this->assertSame(0.7, $som->getWeights()->get(0, 3));
		$this->assertSame(0.7, $som->getWeights()->get(1, 0));
		$this->assertSame(0.7, $som->getWeights()->get(1, 1));
		$this->assertSame(-0.7, $som->getWeights()->get(1, 2));
		$this->assertSame(-0.7, $som->getWeights()->get(1, 3));

		$som->setWeights(Matrix::createZero(2, 4));
		$trainer->setForceWinner(true);
		$trainer->iteration();

		$this->assertSame(0.0, $som->getWeights()->get(0, 0));
		$this->assertSame(0.0, $som->getWeights()->get(0, 1));
		$this->assertSame(0.0, $som->getWeights()->get(0, 2));
		$this->assertSame(0.0, $som->getWeights()->get(0, 3));
		$this->assertSame(-1.0, $som->getWeights()->get(1, 0));
		$this->assertSame(-1.0, $som->getWeights()->get(1, 1));
		$this->assertSame(1.0, $som->getWeights()->get(1, 2));
		$this->assertSame(1.0, $som->getWeights()->get(1, 3));

		$som = new SOM(1, 1);
		$trainer = new BasicTrainSOM($som, 0.1, new BasicMLDataSet([[1.0]]), new Single());
		$trainer->setForceWinner(true);
		$trainer->iteration();

		$this->assertSame(0.1, $som->getWeights()->get(0, 0));
	}

	public function testAutoDecay() {
		$som = new SOM(2, 2);
		$trainer = new BasicTrainSOM($som, 0.01, new BasicMLDataSet([[1,2],[3,4]], null), new Bubble(1));
		$neighbour = $trainer->getNeighborhood();

		for ($i = 0; $i < 100; $i++) {
			$trainer->autoDecay();
		}

		$this->assertSame(0.01, $trainer->getLearningRate());
		$this->assertSame(0.0, $neighbour->getRadius());

		$trainer->setAutoDecay(10, 1, 0.1, 1, 0.1);
		$trainer->autoDecay();

		for ($i = 0; $i < 100; $i++) {
			$trainer->autoDecay();
		}

		$this->assertSame(0.01, $trainer->getLearningRate());
		$this->assertSame(0.01, $neighbour->getRadius());
	}

	public function testBasicProperties() {
		$som = new SOM(2, 2);
		$trainer = new BasicTrainSOM($som, 1, null, new Single());
		$trainer->setForceWinner(true);
		$trainer->setLearningRate(0.1);

		$this->assertEquals($som->getInputCount(), $trainer->getInputNeuronCount());
		$this->assertEquals($som->getOutputCount(), $trainer->getOutputNeuronCount());
		$this->assertSame(0.1, $trainer->getLearningRate());
		$this->assertTrue($trainer->isForceWinner());
	}

	public function testToString() {
		$this->assertEquals("Rate=0.10, Radius=0.00",
			new BasicTrainSOM(new SOM(1,1), 0.1, null, new Single()));
	}

	public function testGetMethod() {
		$this->assertInstanceOf(SOM::class,
			(new BasicTrainSOM(new SOM(1,1), 0.1, null, new Single()))->getMethod());
	}

	public function testPause() {
		$trainer = new BasicTrainSOM(new SOM(1,1), 0.1, null, new Single());
		$this->assertFalse($trainer->canContinue());
		$this->expectException(NeuralNetworkError::class);
		$this->expectExceptionMessage("Training cannot be paused.");
		$trainer->pause();
	}
}
