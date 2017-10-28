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
namespace encog\test\ml\data\specific;

use encog\ml\data\MLDataPair;
use encog\ml\data\specific\CSVMLDataSet;
use encog\test\util\csv\MemoryStream;
use encog\util\csv\CSVFormat;
use PHPUnit\Framework\TestCase;

class CSVMLDataSetTest extends TestCase {
	public function testGetFileName() {
		$this->assertEquals("memory://foo.csv", (new CSVMLDataSet("memory://foo.csv", 2, 1, false))->getFileName());
	}

	public function testGetFormat() {
		$this->assertEquals(CSVFormat::$decimalPoint,
			(new CSVMLDataSet("memory://foo.csv", 2, 1, false, CSVFormat::$decimalPoint))->getFormat());
		$this->assertEquals(CSVFormat::$english,
			(new CSVMLDataSet("memory://foo.csv", 2, 1, false))->getFormat());
	}

	public function testIterator() {
		$dataset = new CSVMLDataSet("memory://foo.csv", 2, 1, false);
		/** @var MLDataPair $pair */
		foreach ($dataset as $key => $pair) {
			$this->assertEquals(1+$key, $pair->getInputArray()[0]);
			$this->assertEquals(2+$key, $pair->getInputArray()[1]);
			$this->assertEquals(3+$key, $pair->getIdealArray()[0]);
		}
	}

	public function setUp() {
		MemoryStream::put("foo.csv", "1,2,3\r\n2,3,4\r\n3,4,5");
	}
}
