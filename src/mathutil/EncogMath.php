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

use encog\Encog;

/**
 * Several useful math functions for Encog.
 */
final class EncogMath {
	public static function hypot(float $a, float $b): float {
		$r = 0.0;
		if (abs($a) > abs($b)) {
			$r = $b / $a;
			$r = abs($a) * sqrt(1+$r*$r);
		} else if ($b != 0) {
			$r = $a / $b;
			$r = abs($b) * sqrt(1+$r*$r);
		}
		return $r;
	}

	/**
	 * WARNING Better just use "$value <=> 0"
	 * @param float $value
	 * @return int
	 */
	public static function sign(float $value): int {
		if (abs($value) < Encog::DEFAULT_DOUBLE_EQUAL) {
			return 0;
		} else if ($value > 0) {
			return 1;
		} else {
			return -1;
		}
	}

	private function __construct() {
	}
}
