<?php
/**
 * Copyright 2015-2016 Tim Jurcka <sdgoij@gmail.com>
 *
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

use encog\mathutil\rbf\MultiquadricFunction;
use PHPUnit_Framework_TestCase as TestCase;

class MultiquadricFunctionTest extends TestCase {
	public function testCalculateSingleDimensional() {
		$rbf = MultiquadricFunction::createSingleDimensional(0.5, 1.0, 1.0);
		$this->assertEquals(1.118033988749895, $rbf->calculate([1,2,3]));
		$this->assertEquals(2.692582403567252, $rbf->calculate([3,2,1]));
		$this->assertEquals(1, $rbf->getDimensions());
	}

	public function testCalculateZeroCentered() {
		$rbf = MultiquadricFunction::createZeroCentered(1);
		$this->assertEquals(1.4142135623730951, $rbf->calculate([1,2,3]));
		$this->assertEquals(3.1622776601683795, $rbf->calculate([3,2,1]));
		$this->assertEquals(1, $rbf->getDimensions());

		$rbf = MultiquadricFunction::createZeroCentered(3);
		$this->assertEquals(4.123105625617661, $rbf->calculate([1,2,3,4,5]));
		$this->assertEquals(7.280109889280518, $rbf->calculate([5,4,3,2,1]));
		$this->assertEquals(3, $rbf->getDimensions());
	}
}
