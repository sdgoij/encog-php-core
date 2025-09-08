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
namespace encog\test\mathutil;

use encog\EncogError;
use encog\mathutil\Equilateral;
use PHPUnit\Framework\TestCase;

class EquilateralTest extends TestCase {
	public function testDecode() {
		$eq = new Equilateral(3, 0, 1);
		$this->assertEquals(2, $eq->decode([0,0]));
		$this->assertEquals(2, $eq->decode([1,0]));
		$this->assertEquals(0, $eq->decode([1,1]));
		$this->assertEquals(1, $eq->decode([0,1]));
	}

	public function testEncode() {
		$eq = new Equilateral(3, 0, 1);
		$this->assertEquals([0.9330127018922193, 0.75], $eq->encode(0));
		$this->assertEquals([0.0669872981077807, 0.75], $eq->encode(1));
		$this->assertEquals([0.5, 0], $eq->encode(2));

		$this->expectException(EncogError::class);
		$this->expectExceptionMessage("Class out of range for equilateral: 3");
		$eq->encode(3);
	}
}
