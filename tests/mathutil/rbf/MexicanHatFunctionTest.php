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
namespace encog\test\mathutil\rbf;

use encog\mathutil\rbf\MexicanHatFunction;
use PHPUnit\Framework\TestCase;

class MexicanHatFunctionTest extends TestCase {
	public function testCalculateSingleDimensional() {
		$rbf = MexicanHatFunction::createSingleDimensional(0.5, 1.0, 1.0);
		$this->assertEquals(0.8219864299617914, $rbf->calculate([1,2,3]));
		$this->assertEquals(-0.4454241976960828, $rbf->calculate([3,2,1]));
		$this->assertEquals(1, $rbf->getDimensions());
	}

	public function testCalculateZeroCentered() {
		$rbf = MexicanHatFunction::createZeroCentered(1);
		$this->assertEquals(0.38940039153570244, $rbf->calculate([1,2,3]));
		$this->assertEquals(-0.36889728596652516, $rbf->calculate([3,2,1]));
		$this->assertEquals(1, $rbf->getDimensions());

		$rbf = MexicanHatFunction::createZeroCentered(3);
		$this->assertEquals(-0.181184300533911, $rbf->calculate([1,2,3,4,5]));
		$this->assertEquals(-8.94396761298881E-5, $rbf->calculate([5,4,3,2,1]));
		$this->assertEquals(3, $rbf->getDimensions());
	}
}
