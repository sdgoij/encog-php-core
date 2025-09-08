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
namespace encog\mathutil\matrices;

/**
 * This class contains a number of utility methods used to work with bipolar
 * numbers. A bipolar number is another way to represent binary numbers. The
 * value of true is defined to be one, where as false is defined to be negative
 * one.
 */
final class BiPolarUtil {
	public static function toDouble(bool $value): float {
		return !$value ? -1.0 : 1.0;
	}

	public static function arrayToDouble(array $values): array {
		$result = [];
		foreach ($values as $value) {
			$result[] = self::toDouble($value);
		}
		return $result;
	}

	public static function array2dToDouble(array $values): array {
		$result = [];
		foreach ($values as $value) {
			$result[] = self::arrayToDouble($value);
		}
		return $result;
	}

	public static function fromDouble(float $value): bool {
		return $value > 0 ? true : false;
	}

	public static function fromDoubleArray(array $values): array {
		$result = [];
		foreach ($values as $value) {
			$result[] = self::fromDouble($value);
		}
		return $result;
	}

	public static function fromDoubleArray2d(array $values): array {
		$result = [];
		foreach ($values as $value) {
			$result[] = self::fromDoubleArray($value);
		}
		return $result;
	}

	public static function normalizeBinary(float $value): float {
		return $value > 0 ? 1.0 : 0.0;
	}

	public static function toBinary(float $value): float {
		return ($value+1) / 2.0;
	}

	public static function toBiPolar(float $value): float {
		return 2 * self::normalizeBinary($value) - 1;
	}

	public static function toNormalizedBinary(float $value): float {
		return self::normalizeBinary(self::toBinary($value));
	}

	private function __construct() {
	}
}
