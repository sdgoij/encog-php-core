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
namespace encog\test\neural\som\training\basic;

use encog\ml\data\basic\BasicMLData;
use encog\neural\NeuralNetworkError;
use encog\neural\som\SOM;
use encog\neural\som\training\basic\BestMatchingUnit;
use PHPUnit\Framework\TestCase;

class BestMatchingUnitTest extends TestCase {
	public function testCalculateBMU() {
		$som = new SOM(2,2);
		$som->getWeights()->setRowCol(0, 0, 0.54718502776101);
		$som->getWeights()->setRowCol(0, 1, -0.43816853847269);
		$som->getWeights()->setRowCol(1, 0, -0.18819977770941);
		$som->getWeights()->setRowCol(1, 1, 0.31521783737243);
		$bmu = new BestMatchingUnit($som);

		$this->assertSame(0.0, $bmu->getWorstDistance());
		$this->assertSame(1, $bmu->calculateBMU(new BasicMLData([0,0])));
		$this->assertSame(1, $bmu->calculateBMU(new BasicMLData([0,1])));
		$this->assertSame(0, $bmu->calculateBMU(new BasicMLData([1,0])));
		$this->assertSame(1, $bmu->calculateBMU(new BasicMLData([1,1])));

		$bmu->reset();
		$this->assertSame(0.0, $bmu->getWorstDistance());

		$this->expectException(NeuralNetworkError::class);
		$this->expectExceptionMessage("Can't train SOM with input size of 2 with input data of size 3");
		$bmu->calculateBMU(new BasicMLData([0,0,0]));
	}
}
