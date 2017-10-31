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
namespace encog\test\util;

use encog\util\Operator;
use PHPUnit\Framework\TestCase;

class OperatorTest extends TestCase {
	use PrivateConstructorTest;

	public function testUnsignedRightShift() {
		$this->assertEquals(0, Operator::urshift(1, 1));
		$this->assertEquals(0, Operator::urshift(0, 3));
		$this->assertEquals(0, Operator::urshift(0, -3));
		$this->assertEquals(-3, Operator::urshift(-3, 0));
		$this->assertEquals(1, Operator::urshift(10, 3));
		$this->assertEquals(0, Operator::urshift(10, -3));
		$this->assertEquals(0, Operator::urshift(10, -123));
		$this->assertEquals(536870910, Operator::urshift(-10, 3));
		$this->assertEquals(7, Operator::urshift(-10, -3));
		$this->assertEquals(134217727, Operator::urshift(-10, -123));
		$this->assertEquals(107, Operator::urshift(-672461345, 25));
		$this->assertEquals(0, Operator::urshift(16, 16));
		$this->assertEquals(32, Operator::urshift(32, 32));
		$this->assertEquals(16, Operator::urshift(33, 33));
		$this->assertEquals(64, Operator::urshift(64, 64));
		$this->assertEquals(32, Operator::urshift(65, 65));
		$this->assertEquals(16, Operator::urshift(66, 66));
		$this->assertEquals(8, Operator::urshift(67, 67));
		$this->assertEquals(128, Operator::urshift(128, 128));
		$this->assertEquals(64, Operator::urshift(129, 129));
	}

	protected function getSubjectClassName(): string {
		return Operator::class;
	}
}
