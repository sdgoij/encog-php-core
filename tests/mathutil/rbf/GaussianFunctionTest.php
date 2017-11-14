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

use encog\mathutil\rbf\GaussianFunction;
use PHPUnit\Framework\TestCase;

class GaussianFunctionTest extends TestCase {
	public function testCalculateSingleDimensional() {
		$rbf = GaussianFunction::createSingleDimensional(0.5, 1.0, 1.0);
		$this->assertEquals(0.882496902584595, $rbf->calculate([1,2,3]));
		$this->assertEquals(0.043936933623407, $rbf->calculate([3,2,1]));
		$this->assertEquals(0.882496902584595, $rbf->calculate([1]));
		$this->assertEquals(0.043936933623407, $rbf->calculate([3]));
		$this->assertEquals(1, $rbf->getDimensions());
	}

	public function testCalculateZeroCentered() {
		$rbf = GaussianFunction::createZeroCentered(1);
		$this->assertEquals(0.6065306597126334, $rbf->calculate([1,2,3]));
		$this->assertEquals(0.0111089965382424, $rbf->calculate([3,2,1]));
		$this->assertEquals(0.6065306597126334, $rbf->calculate([1]));
		$this->assertEquals(0.1353352832366127, $rbf->calculate([2]));
		$this->assertEquals(0.0111089965382424, $rbf->calculate([3]));
		$this->assertEquals(1, $rbf->getDimensions());

		$rbf = GaussianFunction::createZeroCentered(3);
		$this->assertEquals(9.118819655545162E-4, $rbf->calculate([1,2,3,4,5]));
		$this->assertEquals(1.38879438649640E-11, $rbf->calculate([5,4,3,2,1]));
		$this->assertEquals(1.38879438649640E-11, $rbf->calculate([5,4,3]));
		$this->assertEquals(9.118819655545162E-4, $rbf->calculate([1,2,3]));
		$this->assertEquals(9.118819655545162E-4, $rbf->calculate([3,2,1]));
		$this->assertEquals(3, $rbf->getDimensions());
	}
}
