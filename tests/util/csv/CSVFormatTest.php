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
namespace encog\test\util\csv;

use encog\util\csv\CSVFormat;
use PHPUnit_Framework_TestCase as TestCase;

class CSVFormatTest extends TestCase {
	public function testDefaultConstructor() {
		$format = new CSVFormat();
		$this->assertEquals($format->getDecimal(), ".");
		$this->assertEquals($format->getSeparator(), ",");

		$format = new CSVFormat("^", "~");
		$this->assertEquals($format->getDecimal(), "^");
		$this->assertEquals($format->getSeparator(), "~");
	}

	public function testDefaultValues() {
		$dp = new CSVFormat(".", ",");
		$dc = new CSVFormat(",", ";");

		$this->assertEquals(CSVFormat::$decimalPoint, $dp);
		$this->assertEquals(CSVFormat::$decimalComma, $dc);
		$this->assertEquals(CSVFormat::$english, $dp);
		$this->assertEquals(CSVFormat::$egFormat, $dp);
	}

	public function testGetDecimalCharacter() {
		$defaultLocale = setlocale(LC_NUMERIC, "0");

		if (!@setlocale(LC_NUMERIC, "en_US")) {
			$this->fail("Unable to set locale to 'en_US'");
		}
		$this->assertEquals(CSVFormat::getDecimalCharacter(), ".");

		if (!@setlocale(LC_NUMERIC, "nl_NL")) {
			$this->fail("Unable to set locale to 'nl_NL'");
		}
		$this->assertEquals(CSVFormat::getDecimalCharacter(), ",");

		setlocale(LC_NUMERIC, $defaultLocale);
	}

	public function testFormat() {
		$this->assertEquals(CSVFormat::$decimalPoint->format(3.1415926535898, 0), "3");
		$this->assertEquals(CSVFormat::$decimalPoint->format(3.1415926535898, 2), "3.14");
		$this->assertEquals(CSVFormat::$decimalPoint->format(3.1415926535898, 4), "3.1416");
		$this->assertEquals(CSVFormat::$decimalPoint->format(3.1415926535898, 100), "3.1415926535898");

		$this->assertEquals(CSVFormat::$decimalComma->format(3.1415926535898, 0), "3");
		$this->assertEquals(CSVFormat::$decimalComma->format(3.1415926535898, 2), "3,14");
		$this->assertEquals(CSVFormat::$decimalComma->format(3.1415926535898, 4), "3,1416");
		$this->assertEquals(CSVFormat::$decimalComma->format(3.1415926535898, 100), "3,1415926535898");

		$this->assertEquals((new CSVFormat("^"))->format(3.1415926535898, 0), "3");
		$this->assertEquals((new CSVFormat("^"))->format(3.1415926535898, 2), "3^14");
		$this->assertEquals((new CSVFormat("^"))->format(3.1415926535898, 4), "3^1416");
		$this->assertEquals((new CSVFormat("^"))->format(3.1415926535898, 100), "3^1415926535898");
	}

	public function testParse() {
		$this->assertEquals(CSVFormat::$decimalPoint->parse("3"), 3);
		$this->assertEquals(CSVFormat::$decimalPoint->parse("3.14"), 3.14);
		$this->assertEquals(CSVFormat::$decimalPoint->parse("3.1416"), 3.1416);
		$this->assertEquals(CSVFormat::$decimalPoint->parse("3.1415926535898"), M_PI);

		$this->assertEquals(CSVFormat::$decimalComma->parse("3"), 3);
		$this->assertEquals(CSVFormat::$decimalComma->parse("3,14"), 3.14);
		$this->assertEquals(CSVFormat::$decimalComma->parse("3,1416"), 3.1416);
		$this->assertEquals(CSVFormat::$decimalComma->parse("3,1415926535898"), M_PI);

		$this->assertEquals((new CSVFormat("^"))->parse("3"), 3);
		$this->assertEquals((new CSVFormat("^"))->parse("3^14"), 3.14);
		$this->assertEquals((new CSVFormat("^"))->parse("3^1415"), 3.1415);
		$this->assertEquals((new CSVFormat("^"))->parse("3^1415926535898"), M_PI);
	}
}
