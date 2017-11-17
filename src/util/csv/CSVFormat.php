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
namespace encog\util\csv;

class CSVFormat {
	public static function getDecimalCharacter(): string {
		return localeconv()['decimal_point'];
	}

	public static function newDecimalPoint(): self {
		return clone self::DecimalPoint();
	}

	public function __construct(string $decimal = ".", string $separator = ",") {
		$this->separator = $separator;
		$this->decimal = $decimal;
	}

	public function getNumberParser(): NumberParser {
		return new class($this) implements NumberParser {
			public function __construct(CSVFormat $format) {
				$this->format = $format;
			}
			public function parse(string $str): NumberFormatter {
				return new class($str, $this->format) implements NumberFormatter {
					public function __construct($value, CSVFormat $format) {
						$this->format = $format;
						$this->value = $value;
					}
					public function floatValue(): float {
						if ($this->format->getDecimal() != ".") {
							$this->value = str_replace($this->format->getDecimal(), ".", $this->value);
						}
						return floatval($this->value);
					}
					public function intValue(): int {
						return intval($this->value);
					}
					private $format;
					private $value;
				};
			}
			private $format;
		};
	}

	public function getDecimal(): string {
		return $this->decimal;
	}

	public function getSeparator(): string {
		return $this->separator;
	}

	public function format(float $v, int $d): string {
		return str_replace(".", $this->getDecimal(), round($v, $d));
	}

	public function parse(string $str): float {
		return $this->getNumberParser()->parse(trim($str))->floatValue();
	}

	public static function DecimalPoint(): self {
		if (!self::$decimalPoint) {
			self::$decimalPoint = new self(".", ",");
		}
		return self::$decimalPoint;
	}

	public static function DecimalComma(): self {
		if (!self::$decimalComma) {
			self::$decimalComma = new self(",", ";");
		}
		return self::$decimalComma;
	}

	public static function English(): self {
		if (!self::$english) {
			self::$english = clone self::DecimalPoint();
		}
		return self::$english;
	}

	public static function EgFormat(): self {
		if (!self::$egFormat) {
			self::$egFormat = clone self::DecimalPoint();
		}
		return self::$egFormat;
	}

	/** @var CSVFormat */
	private static
		$decimalPoint,
		$decimalComma,
		$english,
		$egFormat
	;

	private $separator;
	private $decimal;
}

interface NumberFormatter {
	function floatValue(): float;
	function intValue(): int;
}

interface NumberParser {
	function parse(string $s): NumberFormatter;
}
