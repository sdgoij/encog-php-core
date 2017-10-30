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
namespace encog\test\mathutil\randomize;

use encog\EncogError;
use encog\mathutil\randomize\RandomChoice;
use encog\util\Random;
use PHPUnit\Framework\TestCase;

class RandomChoiceTest extends TestCase {
	public function testGenerateDefaultProbabilities() {
		$this->assertEquals(0, (new RandomChoice([0.0]))->generate(new Random(1)));
		$this->assertEquals(1, (new RandomChoice([0.0,0.0]))->generate(new Random(1)));
		$this->assertEquals(2, (new RandomChoice([0.0,0.0,0.0]))->generate(new Random(1)));
		$this->assertEquals(2, (new RandomChoice([0.07,0.07,0.07]))->generate(new Random(1)));
		$this->assertEquals(1, (new RandomChoice([0.010,0.011]))->generate(new Random(1)));
		$this->assertEquals(0, (new RandomChoice([0.021]))->generate(new Random(1)));
	}

	public function testGenerateInvalidProbabilities() {
		$this->expectException(EncogError::class);
		$this->expectExceptionMessage("Invalid probabilities.");
		(new RandomChoice([]))->generate(new Random(1));
	}
}
