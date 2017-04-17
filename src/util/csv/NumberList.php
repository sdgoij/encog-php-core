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

/**
 * Class used to handle lists of numbers.
 */
final class NumberList {
	public static function fromList(CSVFormat $format, string $str): array {
		$values = explode($format->getSeparator(), $str);
		$formatter = $format->getNumberFormatter();
		foreach ($values as $k => $v) {
			$values[$k] = $formatter->parse($v)->floatValue();
		}
		return $values;
	}

	public static function fromListInt(CSVFormat $format, string $str): array {
		$values = explode($format->getSeparator(), $str);
		$formatter = $format->getNumberFormatter();
		foreach ($values as $k => $v) {
			$values[$k] = $formatter->parse($v)->intValue();
		}
		return $values;
	}

	public static function toList(CSVFormat $format, array $data, int $precision = 20): string {
		foreach ($data as $k => $v) {
			$data[$k] = $format->format($v, $precision);
		}
		return join($format->getSeparator(), $data);
	}

	public static function toListInt(CSVFormat $format, array $data): string {
		foreach ($data as &$value) $value = (int)$value;
		return join($format->getSeparator(), $data);
	}

	private function __construct() {}
}
