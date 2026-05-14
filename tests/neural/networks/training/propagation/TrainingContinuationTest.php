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
namespace encog\test\neural\networks\training\propagation;

use encog\neural\networks\training\propagation\TrainingContinuation;
use PHPUnit\Framework\TestCase;
use TypeError;

class TrainingContinuationTest extends TestCase {
	public function testGetThrowsException() {
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage("Object 'abc' not found.");
		(new TrainingContinuation())->get("abc");
	}

	public function testPut() {
		$continuation = new TrainingContinuation();
		$continuation->put("abc", [1,2,3]);

		$this->assertEquals([1,2,3], $continuation->get("abc"));
	}

	public function testSet() {
		$continuation = new TrainingContinuation();
		$continuation->set("abc", [1,2,3]);
		$continuation->set("def", $continuation);
		$continuation->set("ghi", 1);

		$this->assertEquals([1,2,3], $continuation->get("abc"));
		$this->assertSame($continuation, $continuation->get("def"));
		$this->assertEquals(1, $continuation->get("ghi"));

		$this->assertEquals([
			"abc" => [1,2,3],
			"def" => $continuation,
			"ghi" => 1
		], $continuation->getContents());
	}

	public function testTrainingType() {
		$continuation = new TrainingContinuation();
		$continuation->setTrainingType("abc");
		$this->assertEquals("abc", $continuation->getTrainingType());
		$this->assertEquals((new TrainingContinuation())->getTrainingType(), "");
	}
}
