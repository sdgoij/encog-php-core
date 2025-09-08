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
namespace encog\test\ml\data\versatile\source;

use encog\EncogError;
use encog\ml\data\versatile\sources\CSVDataSource;
use encog\test\util\csv\MemoryStream;
use encog\util\csv\CSVFormat;
use PHPUnit\Framework\TestCase;

class CSVDataSourceTest extends TestCase {
	public function testColumnIndex() {
		$source = new CSVDataSource("memory://test1.csv", true, ",");
		$this->assertEquals(-1, $source->columnIndex("A"));
		$this->assertEquals(-1, $source->columnIndex("B"));
		$this->assertEquals(-1, $source->columnIndex("C"));
		$source->rewind();
		$this->assertEquals(0, $source->columnIndex("A"));
		$this->assertEquals(1, $source->columnIndex("B"));
		$this->assertEquals(2, $source->columnIndex("C"));
		$source = new CSVDataSource("memory://test2.csv", false, ",");
		$this->assertEquals(-1, $source->columnIndex("A"));
		$this->assertEquals(-1, $source->columnIndex("B"));
		$this->assertEquals(-1, $source->columnIndex("C"));
		$source->rewind();
		$this->assertEquals(-1, $source->columnIndex("A"));
		$this->assertEquals(-1, $source->columnIndex("B"));
		$this->assertEquals(-1, $source->columnIndex("C"));
	}

	public function testReadLine() {
		$source = new CSVDataSource("memory://test1.csv", true, ",");
		$source->rewind();

		$this->assertEquals([0,0,0], $source->readLine());
		$this->assertEquals([0,1,1], $source->readLine());
		$this->assertEquals([1,1,0], $source->readLine());
		$this->assertEquals([1,0,1], $source->readLine());
		$this->assertEquals([], $source->readLine());

		$this->expectExceptionMessage("Please call rewind before reading the file.");
		$this->expectException(EncogError::class);
		$source = new CSVDataSource("memory://test1.csv", true, ",");
		$source->readLine();
	}

	public function setUp(): void {
		MemoryStream::put("test1.csv", "A,B,C\r\n0,0,0\r\n0,1,1\r\n1,1,0\r\n1,0,1");
		MemoryStream::put("test2.csv", "0,0,0\r\n0,1,1\r\n1,1,0\r\n1,0,1");
	}
}
