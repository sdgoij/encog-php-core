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
namespace encog\mathutil;

/**
 * A simple class that prevents numbers from getting either too big or too
 * small.
 */
final class BoundNumbers {
	const TOO_SMALL = -1.0E18;
	const TOO_BIG = 1.0E18;

	public static function bound(float $value): float {
		if ($value < self::TOO_SMALL) {
			return self::TOO_SMALL;
		}
		if ($value > self::TOO_BIG) {
			return self::TOO_BIG;
		}
		return $value;
	}

	private function __construct() {
	}
}
