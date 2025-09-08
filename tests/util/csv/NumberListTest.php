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
namespace encog\test\util\csv;

use encog\test\util\PrivateConstructorTest;
use encog\util\csv\CSVFormat;
use encog\util\csv\NumberList;
use PHPUnit\Framework\TestCase;

class NumberListTest extends TestCase {
	use PrivateConstructorTest;

	public function testFromList() {
		$numbers = [
			NumberList::fromList(CSVFormat::DecimalPoint(), "1,2.5,3000"),
			NumberList::fromList(CSVFormat::DecimalComma(), "1;2,5;3000"),
		];
		foreach ($numbers as $data) {
			$this->assertCount(3, $data);
			$this->assertEquals(1, $data[0]);
			$this->assertEquals(2.5, $data[1]);
			$this->assertEquals(3000, $data[2]);
		}
	}

	public function testFromListInt() {
		$numbers = [
			NumberList::fromListInt(CSVFormat::DecimalPoint(), "1,2.5,3000x,false"),
			NumberList::fromListInt(CSVFormat::DecimalComma(), "1;2,5;3000x;false"),
		];
		foreach ($numbers as $data) {
			$this->assertCount(4, $data);
			$this->assertEquals(1, $data[0]);
			$this->assertEquals(2, $data[1]);
			$this->assertEquals(3000, $data[2]);
			$this->assertEquals(0, $data[3]);
		}
	}

	public function testToList() {
		$data = [0.5, 10000, 10.5];

		$this->assertEquals("0.5,10000,10.5", NumberList::toList(CSVFormat::DecimalPoint(), $data));
		$this->assertEquals("0,5;10000;10,5", NumberList::toList(CSVFormat::DecimalComma(), $data));
	}

	public function testToListInt() {
		$data = ["1.5", "10000x", 10.5, true];

		$this->assertEquals("1,10000,10,1", NumberList::toListInt(CSVFormat::DecimalPoint(), $data));
		$this->assertEquals("1;10000;10;1", NumberList::toListInt(CSVFormat::DecimalComma(), $data));
	}

	protected function getSubjectClassName(): string {
		return NumberList::class;
	}
}
