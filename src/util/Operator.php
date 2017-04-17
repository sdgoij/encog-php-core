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
namespace encog\util;

final class Operator {
	public static function urshift($a, $b) {
		$z = hexdec(80000000);
		if ($z & $a) {
			$a = $a >> 1;
			$a &= ~$z;
			$a |= 0x40000000;
			$a = $a >> ($b - 1);
		} else {
			$a = ($a >> $b);
		}
		return $a;
	}
	private function __construct() {
	}
}
