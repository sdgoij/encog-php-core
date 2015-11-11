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
namespace encog\test\ml\data\auto;

use encog\ml\data\auto\AutoFloatColumn;
use OutOfBoundsException;
use PHPUnit_Framework_TestCase as TestCase;

class AutoFloatColumnTest extends TestCase {
	public function testToString() {
		$this->assertEquals(
			sprintf("[%s:min=0.000000,max=1.000000]", AutoFloatColumn::class),
			(string)new AutoFloatColumn([0,1,1,0], 0, 1)
		);
	}

	public function testAutoMinMax() {
		$column = new AutoFloatColumn([0,1,1,0]);
		$this->assertEquals(0, $column->getMin());
		$this->assertEquals(1, $column->getMax());

		$column = new AutoFloatColumn([-1,0,1,2], 42, 42);
		$this->assertEquals(42, $column->getMin());
		$this->assertEquals(42, $column->getMax());
		$column->autoMinMax();

		$this->assertEquals(-1, $column->getMin());
		$this->assertEquals(2, $column->getMax());
	}

	public function testGetData() {
		$this->assertEquals([0,1,2,3], (new AutoFloatColumn([0,1,2,3]))->getData());
	}

	public function testGetDataAt() {
		$column = new AutoFloatColumn([1,2,3,4]);

		$this->assertEquals(1, $column->getDataAt(0));
		$this->assertEquals(2, $column->getDataAt(1));
		$this->assertEquals(3, $column->getDataAt(2));
		$this->assertEquals(4, $column->getDataAt(3));

		$this->expectException(OutOfBoundsException::class);
		$this->expectExceptionMessage("5");
		$column->getDataAt(5);
	}

	public function testGetNormalized() {
		$column = new AutoFloatColumn([0.1,0.2,0.3,0.4]);
		$this->assertEquals(0.0000000000, $column->getNormalized(0, 0.0, 1.0));
		$this->assertEquals(0.3333333333, $column->getNormalized(1, 0.0, 1.0));
		$this->assertEquals(0.6666666666, $column->getNormalized(2, 0.0, 1.0));
		$this->assertEquals(1.0000000000, $column->getNormalized(3, 0.0, 1.0));
	}
}
