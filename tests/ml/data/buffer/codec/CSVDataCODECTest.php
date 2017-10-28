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
namespace encog\test\ml\data\buffer\codec;

use encog\ml\data\buffer\BufferedDataError;
use encog\ml\data\buffer\codec\CSVDataCODEC;
use encog\test\util\csv\MemoryStream;
use encog\util\csv\CSVFormat;
use encog\util\csv\CSVReader;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit\Framework\TestCase;

class CSVDataCODECTest extends TestCase {

	public function testIdealSize() {
		$this->assertEquals(1, CSVDataCODEC::reader("foo.csv", CSVFormat::$english, false, 2, 1, false)->getIdealSize());
		$this->assertEquals(-1, (new CSVDataCODEC())->getIdealSize());
	}

	public function testInputSize() {
		$this->assertEquals(2, CSVDataCODEC::reader("foo.csv", CSVFormat::$english, false, 2, 1, false)->getInputSize());
		$this->assertEquals(-1, (new CSVDataCODEC())->getInputSize());
	}

	public function testPrepareRead() {
		$this->expectExceptionMessage("To import CSV, you must use a constructor that specifies input and ideal sizes.");
		$this->expectException(BufferedDataError::class);
		(new CSVDataCODEC())->prepareRead();
	}

	public function testPrepareWrite() {
		$this->expectExceptionMessage("Unable to open ''");
		$this->expectException(BufferedDataError::class);
		(new CSVDataCODEC())->prepareWrite(1,2,1);
	}

	public function testRead() {
		$input = $ideal = [];
		$significance = 0.0;
		$index = 0;

		$this->assertFalse((new CSVDataCODEC())->read($input, $ideal, $significance));

		MemoryStream::put("test.csv", "1,2,3\r\n2,3,4\r\n3,4,5");
		$codec = CSVDataCODEC::reader("memory://test.csv", CSVFormat::$english, false, 2, 1, false);
		$codec->prepareRead();

		while ($codec->read($input, $ideal, $significance)) {
			$this->assertEquals(1+$index, $input[0]);
			$this->assertEquals(2+$index, $input[1]);
			$this->assertEquals(3+$index, $ideal[0]);
			$index++;
		}
	}

	public function testReadSignificance() {
		$input = $ideal = [];
		$significance = 0.0;
		$index = 0;

		$this->assertFalse((new CSVDataCODEC())->read($input, $ideal, $significance));

		MemoryStream::put("test.csv", "1,2,3,0.1\r\n2,3,4,0.2\r\n3,4,5,0.3");
		$codec = CSVDataCODEC::reader("memory://test.csv", CSVFormat::$english, false, 2, 1, true);
		$codec->prepareRead();

		while ($codec->read($input, $ideal, $significance)) {
			$this->assertEquals((1+$index)/10, $significance);
			$this->assertEquals(1+$index, $input[0]);
			$this->assertEquals(2+$index, $input[1]);
			$this->assertEquals(3+$index, $ideal[0]);
			$index++;
		}
	}

	public function testWrite() {
		MemoryStream::put("test.csv", "");
		$expect = "1,2,3,0.1\r\n2,3,4,0.2\r\n3,4,5,0.3\r\n";
		$codec = CSVDataCODEC::writer("memory://test.csv", CSVFormat::$english, true);
		$codec->prepareWrite(3, 2, 1);
		$codec->write([1,2], [3], 0.1);
		$codec->write([2,3], [4], 0.2);
		$codec->write([3,4], [5], 0.3);

		if (!$fp = fopen("memory://test.csv", "r")) {
			$this->fail("This should not happen.");
		}
		$this->assertEquals($expect, fread($fp, strlen($expect)));
		fclose($fp);
	}

	public function testClose() {
		MemoryStream::put("test.csv", "");
		$codec = CSVDataCODEC::writer("memory://test.csv", CSVFormat::$english, false);
		$codec->prepareWrite(3,2,1);
		$codec->close();

		/** @var CSVReader|MockObject $reader */
		$reader = $this->getMockBuilder(CSVReader::class)->setMethods(['close'])
			->disableOriginalConstructor()
			->getMock();
		$reader->expects($this->once())->method('close');

		CSVDataCODEC::fromCSVReader($reader, 2, 1, false)->close();
	}
}
