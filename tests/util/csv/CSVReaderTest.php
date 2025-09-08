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

use DateTime;
use encog\EncogError;
use encog\util\csv\CSVFormat;
use encog\util\csv\CSVReader;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CSVReaderTest extends TestCase {
	const INPUT_NAME = "memory://test.csv";

	public function testCommaSeparated() {
		MemoryStream::put(self::INPUT_NAME, "one,1\ntwo,2\nthree,3\n");

		$csv = CSVReader::createFromFileName(self::INPUT_NAME, false, CSVFormat::EgFormat());
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

	public function testGetColumnNames() {
		MemoryStream::put(self::INPUT_NAME, "a,b,c");
		$reader = CSVReader::createFromFileName(self::INPUT_NAME, true, ",");
		$this->assertEquals(["a", "b", "c"], $reader->getColumnNames());
	}

	public function testInvalidStreamResource() {
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage("\$reader is not a valid stream resource.");
		new CSVReader(false, false, ',');
	}

	public function testGetFormat() {
		MemoryStream::put(self::INPUT_NAME, "a,b,c");
		$reader = CSVReader::createFromFileName(self::INPUT_NAME, false, CSVFormat::EgFormat());
		$this->assertSame(CSVFormat::EgFormat(), $reader->getFormat());
	}

	public function testHasMissing() {
		MemoryStream::put(self::INPUT_NAME, join("\n", [
			"a,b,c",
			"a,?,c",
			"\"\", ?, ?",
			"\"a\",b,c",
		]));

		$reader = CSVReader::createFromFileName(self::INPUT_NAME, false, ",");
		$this->assertTrue($reader->next());

		$this->assertFalse($reader->hasMissing());
		$this->assertTrue($reader->next());

		$this->assertTrue($reader->hasMissing());
		$this->assertTrue($reader->next());

		$this->assertTrue($reader->hasMissing());
		$this->assertTrue($reader->next());

		$this->assertFalse($reader->hasMissing());
		$this->assertFalse($reader->next());
	}

	public function testParseDate() {
		$diff = (new DateTime("30011111"))->diff(CSVReader::parseDate("30011111"));
		$this->assertSame(0, $diff->y);
		$this->assertSame(0, $diff->m);
		$this->assertSame(0, $diff->d);
	}

	public function testDisplayDate() {
		$this->assertEquals("30011111", CSVReader::displayDate(new DateTime("3001/11/11")));
	}

	public function testGetInvalidColumn() {
		MemoryStream::put(self::INPUT_NAME, "a,b,c");
		$reader = CSVReader::createFromFileName(self::INPUT_NAME, false, ",");
		$this->assertTrue($reader->next());
		$this->assertEquals(3, $reader->getColumnCount());

		$this->expectException(EncogError::class);
		$this->expectExceptionMessageMatches("/^Can't access column \[.*\] in a file that has only .* columns/");
		$reader->get(3);
	}

	public function testGetColumnByName() {
		MemoryStream::put(self::INPUT_NAME, join("\n", ["a,b,c", "2111/01/01,1.1,2"]));
		$reader = CSVReader::createFromFileName(self::INPUT_NAME, true, ",");
		$this->assertTrue($reader->next());

		$this->assertSame("2111/01/01", $reader->get("a"));
		$this->assertSame((new DateTime("21110101"))->getTimestamp(), $reader->getDate("A")->getTimestamp());
		$this->assertSame(1.1, $reader->getDouble("b"));
		$this->assertSame("1.1", $reader->get("B"));
		$this->assertSame(2, $reader->getInt("c"));
		$this->assertSame("2", $reader->get("C"));
		$this->assertSame("", $reader->get("Z"));

		$this->assertFalse($reader->next());
	}

	public function testGetInvalidDate() {
		MemoryStream::put(self::INPUT_NAME, "a,b,c");
		$reader = CSVReader::createFromFileName(self::INPUT_NAME, false, " ");
		$this->assertTrue($reader->next());

		$this->expectException(EncogError::class);
		$this->expectExceptionMessageMatches("/failed to parse time string \(a,b,c\) at position/i");
		$reader->getDate(0);
	}
}
