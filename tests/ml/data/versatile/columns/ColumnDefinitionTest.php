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
namespace encog\test\ml\data\versatile\columns;

use encog\EncogError;
use encog\ml\data\MLDataError;
use encog\ml\data\versatile\columns\ColumnDefinition;
use encog\ml\data\versatile\columns\ColumnType;
use encog\ml\data\versatile\NormalizationHelper;
use PHPUnit\Framework\TestCase;

class ColumnDefinitionTest extends TestCase {
	public function testCreateColumn() {
		$nominal = new ColumnType(ColumnType::nominal);
		$ignore = new ColumnType(ColumnType::ignore);
		$column = new ColumnDefinition("A", $nominal);
		$this->assertEquals("A", $column->getName());
		$this->assertEquals($nominal, $column->getType());
		$this->assertTrue(is_nan($column->getLow()));
		$this->assertTrue(is_nan($column->getHigh()));
		$this->assertTrue(is_nan($column->getMean()));
		$this->assertTrue(is_nan($column->getSd()));
		$this->assertEquals(-1, $column->getIndex());
		$this->assertNull($column->getOwner());

		$column->setName("X");
		$column->setIndex(0);
		$column->setHigh(1.0);
		$column->setLow(0.0);
		$column->setMean(0.5);
		$column->setSd(0.3);
		$column->setType($ignore);
		$column->setOwner(new NormalizationHelper());
		$column->setCount(100);

		$this->assertEquals("X", $column->getName());
		$this->assertEquals(0, $column->getIndex());
		$this->assertEquals(1.0, $column->getHigh());
		$this->assertEquals(0.0, $column->getLow());
		$this->assertEquals(0.5, $column->getMean());
		$this->assertEquals(0.3, $column->getSd());
		$this->assertEquals($ignore, $column->getType());
		$this->assertInstanceOf(NormalizationHelper::class, $column->getOwner());
		$this->assertEquals(100, $column->getCount());

	}

	public function testClasses() {
		$column = new ColumnDefinition("A", new ColumnType(ColumnType::nominal));
		$this->assertEquals([], $column->getClasses());

		$column->defineClass("a");
		$column->defineClass("b");
		$column->defineClass("c");

		$this->assertEquals(["a", "b", "c"], $column->getClasses());
		$column->setClasses([]);
		$this->assertEquals([], $column->getClasses());
	}

	public function testToString() {
		$expect[] = "[ColumnDefinition:A(continuous);low=NaN,high=NaN,mean=NaN,sd=NaN]";
		$expect[] = "[ColumnDefinition:A(ordinal);A,B,C]";

		$column1 = new ColumnDefinition("A", new ColumnType(ColumnType::continuous));
		$column2 = new ColumnDefinition("A", new ColumnType(ColumnType::ordinal));
		$column2->setClasses(["A", "B", "C"]);

		$this->assertEquals($expect[0], (string)$column1);
		$this->assertEquals($expect[1], (string)$column2);
	}

	public function testAnalyzeContinuous() {
		$column = new ColumnDefinition("A", new ColumnType(ColumnType::continuous));
		$column->setOwner(new NormalizationHelper());
		$column->analyze("1");
		$column->analyze("2");
		$column->analyze("3");

		$this->assertEquals(1, $column->getLow());
		$this->assertEquals(3, $column->getHigh());
		$this->assertEquals(6, $column->getMean());
		$this->assertEquals(0, $column->getSd());

		$this->expectExceptionMessage("Column has no owner.");
		$this->expectException(MLDataError::class);
		(new ColumnDefinition("A", new ColumnType(ColumnType::continuous)))->analyze("1");
	}

	public function testAnalyzeOrdinal() {
		$column = new ColumnDefinition("A", new ColumnType(ColumnType::ordinal));
		$column->setClasses(["A", "B", "C"]);
		$column->analyze("A");
		$column->analyze("B");
		$column->analyze("C");

		$this->assertEquals(["A", "B", "C"], $column->getClasses());
		$this->assertTrue(is_nan($column->getLow()));
		$this->assertTrue(is_nan($column->getHigh()));
		$this->assertTrue(is_nan($column->getMean()));
		$this->assertTrue(is_nan($column->getSd()));

		$this->expectExceptionMessage(
			"You must predefine any ordinal values (in order). Undefined ordinal value: D");
		$this->expectException(EncogError::class);
		$column->analyze("D");
	}

	public function testAnalyzeNominal() {
		$column = new ColumnDefinition("A", new ColumnType(ColumnType::nominal));
		$column->analyze("A");
		$column->analyze("B");
		$column->analyze("C");

		$this->assertEquals(["A", "B", "C"], $column->getClasses());
	}

	public function testEquals() {
		$column1 = new ColumnDefinition("A", new ColumnType(ColumnType::nominal));
		$column2 = new ColumnDefinition("A", new ColumnType(ColumnType::nominal));
		$column3 = new ColumnDefinition("B", new ColumnType(ColumnType::nominal));
		$column4 = new ColumnDefinition("B", new ColumnType(ColumnType::nominal));
		$column4->analyze("A");

		$this->assertTrue($column1->equals($column2));
		$this->assertFalse($column2->equals($column3));
		$this->assertFalse($column3->equals($column4));
		$this->assertFalse($column4->equals(1));
	}
}
