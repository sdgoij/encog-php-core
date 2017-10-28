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
namespace encog\test\mathutil;

use encog\mathutil\EncogMath;
use encog\test\util\PrivateConstructorTest;
use PHPUnit_Framework_TestCase as TestCase;

class EncogMathTest extends TestCase {
	use PrivateConstructorTest;

	public function testHypot() {
		$this->assertEquals(0, EncogMath::hypot(0, 0));
		$this->assertEquals(1, EncogMath::hypot(1, 0));
		$this->assertEquals(1.4142135623730951, EncogMath::hypot(1, 1));
		$this->assertEquals(4.442882938158366, EncogMath::hypot(M_PI, -M_PI));
	}

	public function testSign() {
		$this->assertEquals(-1, EncogMath::sign(-1));
		$this->assertEquals(0, EncogMath::sign(0));
		$this->assertEquals(1, EncogMath::sign(1));

		$this->assertEquals(-1, -1 <=> 0);
		$this->assertEquals(0, 0 <=> 0);
		$this->assertEquals(1, 1 <=> 0);
	}

	protected function getSubjectClassName(): string {
		return EncogMath::class;
	}
}
