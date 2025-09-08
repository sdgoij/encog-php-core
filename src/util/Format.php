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
namespace encog\util;

/**
 * Provides the ability for Encog to format numbers and times.
 */
final class Format {

	const MEMORY_KB = 1024;
	const MEMORY_MB = 1024 * self::MEMORY_KB;
	const MEMORY_GB = 1024 * self::MEMORY_MB;
	const MEMORY_TB = 1024 * self::MEMORY_GB;

	const TIME_MS     = 1000;
	const TIME_MINUTE = 60;
	const TIME_HOUR   = 60 * self::TIME_MINUTE;
	const TIME_DAY    = 24 * self::TIME_HOUR;

	const HUNDRED_PERCENT = 100.0;

	public static function formatDouble(float $value, int $precision): string {
		return sprintf("%0.{$precision}f", $value);
	}

	public static function formatInteger(int $value): string {
		return (string)$value;
	}

	public static function formatDateSpan(int $value): string {
		$seconds = $value;
		$days = (int)($seconds/self::TIME_DAY);
		$seconds -= $days * self::TIME_DAY;
		$hours = (int)($seconds/self::TIME_HOUR);
		$seconds -= $hours * self::TIME_HOUR;
		$minutes = (int)($seconds/self::TIME_MINUTE);
		$seconds -= $minutes * self::TIME_MINUTE;
		return sprintf("%d %s %'.02d:%'.02d:%'.02d",
			$days,
			$days > 1 ? "days" : "day",
			$hours,
			$minutes,
			$seconds
		);
	}

	private function __construct() {
	}
}
