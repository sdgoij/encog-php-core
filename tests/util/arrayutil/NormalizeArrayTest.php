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
namespace encog\test\util\arrayutil;

use encog\util\arrayutil\NormalizeArray;
use encog\util\arrayutil\NormalizedField;
use PHPUnit\Framework\TestCase;
use ReflectionObject;

class NormalizeArrayTest extends TestCase {
	public function testProcessArray() {
		$field = $this->createMock(NormalizedField::class);
		$field->expects($this->exactly(3))
			->method("analyze")
			->withConsecutive([1], [2], [3]);
		$field->expects($this->exactly(3))
			->method("normalize")
			->withConsecutive([1], [2], [3])
			->willReturn(42, 42, 42);

		$normalizer = new NormalizeArray();
		$this->injectFieldMock($normalizer, $field);

		$this->assertEquals([42, 42, 42], $normalizer->process([1, 2, 3]));
		$this->assertSame($field, $normalizer->getField());
	}

	public function testHighLow() {
		$field = $this->createMock(NormalizedField::class);
		$field->expects($this->once())
			->method("getNormalizedHigh")
			->willReturn(1.0);
		$field->expects($this->once())
			->method("getNormalizedLow")
			->willReturn(-1.0);
		$field->expects($this->once())
			->method("setNormalizedHigh")
			->with(0.9);
		$field->expects($this->once())
			->method("setNormalizedLow")
			->with(0.1);

		$normalizer = new NormalizeArray();
		$this->injectFieldMock($normalizer, $field);

		$this->assertEquals(1.0, $normalizer->getNormalizedHigh());
		$this->assertEquals(-1.0, $normalizer->getNormalizedLow());

		$normalizer->setNormalizedHigh(0.9);
		$normalizer->setNormalizedLow(0.1);
	}

	private function injectFieldMock(NormalizeArray $normalizer, $mock) {
		$field = (new ReflectionObject($normalizer))->getProperty("field");
		$field->setAccessible(true);
		$field->setValue($normalizer, $mock);
		$field->setAccessible(false);
	}
}
