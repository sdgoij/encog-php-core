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
namespace encog\mathutil;

/**
 * PHP will sometimes return NaN or Infinity when numbers get to large or too
 * small. This can have undesirable effects. This class provides some basic math
 * functions that may be in danger of returning such a value. This class imposes
 * a very large and small ceiling and floor to keep the numbers within range.
 */
final class BoundMath {
	public static function cos(float $value): float { return BoundNumbers::bound(cos($value)); }
	public static function exp(float $value): float { return BoundNumbers::bound(exp($value)); }
	public static function log(float $value): float { return BoundNumbers::bound(log($value)); }
	public static function pow(float $value, $exponent): float { return BoundNumbers::bound(pow($value, $exponent)); }
	public static function sin(float $value): float { return BoundNumbers::bound(sin($value)); }
	public static function sqrt(float $value): float { return BoundNumbers::bound(sqrt($value)); }
	public static function tanh(float $value): float { return BoundNumbers::bound(tanh($value)); }
	private function __construct() {}
}
