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
namespace encog\test\mathutil\matrices;

use encog\mathutil\matrices\BiPolarUtil;
use encog\test\util\PrivateConstructorTest;
use PHPUnit\Framework\TestCase;

class BiPolarUtilTest extends TestCase {
	use PrivateConstructorTest;

	protected function getSubjectClassName(): string {
		return BiPolarUtil::class;
	}

	public function testToDouble() {
		$this->assertEquals(-1.0, BiPolarUtil::toDouble(false));
		$this->assertEquals(1.0, BiPolarUtil::toDouble(true));
	}

	public function testArrayToDouble() {
		$this->assertEquals([-1.0, 1.0, -1.0], BiPolarUtil::arrayToDouble([false, true, false]));
		$this->assertEquals([1.0, -1.0, 1.0], BiPolarUtil::arrayToDouble([true, false, true]));
	}

	public function testArray2dToDouble() {
		$this->assertEquals([[-1.0], [1.0], [-1.0]], BiPolarUtil::array2dToDouble([[false], [true], [false]]));
		$this->assertEquals([[1.0], [-1.0], [1.0]], BiPolarUtil::array2dToDouble([[true], [false], [true]]));
	}

	public function testFromDouble() {
		$this->assertFalse(BiPolarUtil::fromDouble(-1));
		$this->assertFalse(BiPolarUtil::fromDouble(-1/3));
		$this->assertTrue(BiPolarUtil::fromDouble(1/3));
		$this->assertTrue(BiPolarUtil::fromDouble(1));
	}

	public function testFromDoubleArray() {
		$this->assertEquals([false, true, false], BiPolarUtil::fromDoubleArray([-1.0, 1.0, -1.0]));
		$this->assertEquals([true, false, true], BiPolarUtil::fromDoubleArray([1.0, -1.0, 1.0]));
	}

	public function testFromDoubleArray2d() {
		$this->assertEquals([[false], [true], [false]], BiPolarUtil::fromDoubleArray2d([[-1.0], [1.0], [-1.0]]));
		$this->assertEquals([[true], [false], [true]], BiPolarUtil::fromDoubleArray2d([[1.0], [-1.0], [1.0]]));
	}

	public function testNormalizeBinary() {
		$this->assertEquals(0.0, BiPolarUtil::normalizeBinary(-10));
		$this->assertEquals(1.0, BiPolarUtil::normalizeBinary(10));
	}

	public function testToBinary() {
		$this->assertEquals(0.0, BiPolarUtil::toBinary(-1));
		$this->assertEquals(0.5, BiPolarUtil::toBinary(0));
		$this->assertEquals(1.0, BiPolarUtil::toBinary(1));
	}

	public function testToBiPolar() {
		$this->assertEquals(-1.0, BiPolarUtil::toBiPolar(-10));
		$this->assertEquals(1.0, BiPolarUtil::toBiPolar(10));
	}

	public function testToNormalizedBinary() {
		$this->assertEquals(0.0, BiPolarUtil::toNormalizedBinary(-10));
		$this->assertEquals(1.0, BiPolarUtil::toNormalizedBinary(10));
	}
}
