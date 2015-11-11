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
namespace encog\test\util\csv;

use encog\util\csv\CSVFormat;
use encog\util\csv\CSVReader;
use PHPUnit_Framework_TestCase as TestCase;

class CSVReaderTest extends TestCase {
	const INPUT_NAME = "memory://test.csv";

	public function testCommaSeparated() {
		MemoryStream::put(self::INPUT_NAME, "one,1\ntwo,2\nthree,3\n");

		$csv = CSVReader::createFromFileName(self::INPUT_NAME, false, CSVFormat::$egFormat);
		$this->assertTrue($csv->next());

		$this->assertEquals("one", $csv->get(0));
		$this->assertEquals("1", $csv->get(1));
		$this->assertTrue($csv->next());

		$this->assertEquals("two", $csv->get(0));
		$this->assertEquals("2", $csv->get(1));
		$this->assertTrue($csv->next());

		$this->assertEquals("three", $csv->get(0));
		$this->assertEquals("3", $csv->get(1));
		$this->assertFalse($csv->next());
		$csv->close();
	}

	public function testSpaceSeparated() {
		MemoryStream::put(self::INPUT_NAME, join("\n", [
			"one 1 \"test one two three\"",
			"two\t2 \"test one two three\"",
			"three  3  \"test one two three\"",
		]));

		$csv = CSVReader::createFromFileName(self::INPUT_NAME, false, new CSVFormat(".", " "));
		$this->assertTrue($csv->next());

		$this->assertEquals(3, $csv->getColumnCount());
		$this->assertEquals("one", $csv->get(0));
		$this->assertEquals("1", $csv->get(1));
		$this->assertEquals("test one two three", $csv->get(2));
		$this->assertTrue($csv->next());

		$this->assertEquals("two", $csv->get(0));
		$this->assertEquals("2", $csv->get(1));
		$this->assertTrue($csv->next());

		$this->assertEquals("three", $csv->get(0));
		$this->assertEquals("3", $csv->get(1));
		$this->assertFalse($csv->next());
	}
}
