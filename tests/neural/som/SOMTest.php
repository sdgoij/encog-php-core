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
namespace encog\test\neural\som;

use encog\mathutil\matrices\Matrix;
use encog\ml\data\basic\BasicMLData;
use encog\ml\data\basic\BasicMLDataSet;
use encog\neural\NeuralNetworkError;
use encog\neural\som\SOM;
use PHPUnit\Framework\TestCase;

class SOMTest extends TestCase {
	public function testClassify() {
		$weights = Matrix::createZero(2,2);
		$weights->setRowCol(0, 0, 1.0);
		$weights->setRowCol(0, 1, 2.0);
		$weights->setRowCol(1, 0, 3.0);
		$weights->setRowCol(1, 1, 4.0);

		$som = new SOM(2, 2);
		$som->reset();
		$som->setWeights($weights);

		$this->assertSame(0, $som->classify(new BasicMLData([1])));
		$this->assertSame(0, $som->classify(new BasicMLData([2])));
		$this->assertSame(1, $som->classify(new BasicMLData([3])));
		$this->assertSame(1, $som->classify(new BasicMLData([4])));

		$this->assertSame(
			$som->classify(new BasicMLData([1])),
			$som->winner(new BasicMLData([1]))
		);

		$this->expectException(NeuralNetworkError::class);
		$this->expectExceptionMessage("Can't classify SOM with input size of 2 with input data of size 3");
		$som->classify(new BasicMLData([1,2,3]));
	}

	public function testCalculateError() {
		$som = new SOM(2, 2);
		$w = $som->getWeights();
		$w->setRowCol(0, 0, 1.0);
		$w->setRowCol(0, 1, 2.0);
		$w->setRowCol(1, 0, 3.0);
		$w->setRowCol(1, 1, 4.0);

		$this->assertSame(0.0, $som->calculateError(new BasicMLDataSet([[1,2],[3,4]])));
		$this->assertSame(0.01, $som->calculateError(new BasicMLDataSet([[1,3],[2,4]])));
		$this->assertSame(0.01414213562373095, $som->calculateError(new BasicMLDataSet([[4,3],[2,1]])));
		$this->assertSame(0.022360679774997897, $som->calculateError(new BasicMLDataSet([[0,0],[0,0]])));
	}
}
