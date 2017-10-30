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

use encog\mathutil\BoundMath;
use encog\test\util\PrivateConstructorTest;
use PHPUnit\Framework\TestCase;

class BoundMathTest extends TestCase {
	use PrivateConstructorTest;

	public function testBoundMath() {
		$this->assertEquals(cos(2), BoundMath::cos(2));
		$this->assertEquals(exp(2), BoundMath::exp(2));
		$this->assertEquals(log(2), BoundMath::log(2));
		$this->assertEquals(pow(2, 2), BoundMath::pow(2, 2));
		$this->assertEquals(sin(2), BoundMath::sin(2));
		$this->assertEquals(sqrt(2), BoundMath::sqrt(2));
		$this->assertEquals(tanh(2), BoundMath::tanh(2));
	}

	protected function getSubjectClassName(): string {
		return BoundMath::class;
	}
}
