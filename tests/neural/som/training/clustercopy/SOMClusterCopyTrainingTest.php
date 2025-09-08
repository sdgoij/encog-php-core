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
namespace encog\test\neural\som\training\clustercopy;

use encog\mathutil\matrices\Matrix;
use encog\ml\data\basic\BasicMLDataSet;
use encog\neural\NeuralNetworkError;
use encog\neural\som\SOM;
use encog\neural\som\training\clustercopy\SOMClusterCopyTraining;
use PHPUnit\Framework\TestCase;

class SOMClusterCopyTrainingTest extends TestCase {
	public function testTrainingIteration() {
		$trainer = new SOMClusterCopyTraining(new SOM(2,2), new BasicMLDataSet([[1,2],[3,4]]));
		$this->assertFalse($trainer->isTrainingDone());
		$trainer->iteration();

		/** @var SOM $som */
		$som = $trainer->getMethod();
		assert($som instanceof SOM);
		$weights = $som->getWeights();

		$this->assertSame(1.0, $weights->get(0,0));
		$this->assertSame(2.0, $weights->get(0,1));
		$this->assertSame(3.0, $weights->get(1,0));
		$this->assertSame(4.0, $weights->get(1,1));

		$this->assertTrue($trainer->isTrainingDone());
	}

	public function testOutputSize() {
		$this->expectException(NeuralNetworkError::class);
		$this->expectExceptionMessage("To use cluster copy training you must have at least as many output neurons as training elements.");
		(new SOMClusterCopyTraining(new SOM(2,2), new BasicMLDataSet([[1],[2],[3]])));
	}

	public function testGetMethod() {
		$this->assertInstanceOf(SOM::class,
			(new SOMClusterCopyTraining(new SOM(1,1), new BasicMLDataSet([[]])))->getMethod());
	}

	public function testPause() {
		$trainer = new SOMClusterCopyTraining(new SOM(2,2), new BasicMLDataSet([[]]));
		$this->assertFalse($trainer->canContinue());
		$this->expectException(NeuralNetworkError::class);
		$this->expectExceptionMessage("Training cannot be paused.");
		$trainer->pause();
	}
}
