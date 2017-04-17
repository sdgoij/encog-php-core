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

use encog\mathutil\rbf\InverseMultiquadricFunction;
use PHPUnit_Framework_TestCase as TestCase;

class InverseMultiquadricFunctionTest extends TestCase {
	public function testCalculateSingleDimensional() {
		$rbf = InverseMultiquadricFunction::createSingleDimensional(0.5, 1.0, 1.0);
		$this->assertEquals(0.8944271909999159, $rbf->calculate([1,2,3]));
		$this->assertEquals(0.3713906763541037, $rbf->calculate([3,2,1]));
		$this->assertEquals(1, $rbf->getDimensions());
	}

	public function testCalculateZeroCentered() {
		$rbf = InverseMultiquadricFunction::createZeroCentered(1);
		$this->assertEquals(0.7071067811865475, $rbf->calculate([1,2,3]));
		$this->assertEquals(0.3162277660168379, $rbf->calculate([3,2,1]));
		$this->assertEquals(1, $rbf->getDimensions());

		$rbf = InverseMultiquadricFunction::createZeroCentered(3);
		$this->assertEquals(0.24253562503633297, $rbf->calculate([1,2,3,4,5]));
		$this->assertEquals(0.13736056394868904, $rbf->calculate([5,4,3,2,1]));
		$this->assertEquals(3, $rbf->getDimensions());
	}
}
