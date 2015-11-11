<?php
/**
 * Copyright 2015-2016 Tim Jurcka <sdgoij@gmail.com>
 *
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
namespace encog\mathutil\randomize;

use encog\util\Random;

/**
 * A randomizer that will create random weight and bias values that are between
 * a specified range.
 */
class RangeRandomizer extends BasicRandomizer {
	public static function randomInt(int $min, int $max, Random $r = null): int {
		return (int)self::randomFloat((float)$min, (float)$max+1, $r);
	}

	public static function randomFloat(float $min, float $max, Random $r = null): float {
		$rand = $r === null
			? function(): float { return mt_rand()/mt_getrandmax(); }
			: function() use($r): float { return $r->nextDouble(); };
		return ($max-$min) * $rand() + $min;
	}

	public function __construct(float $min, float $max) {
		parent::__construct();
		$this->min = $min;
		$this->max = $max;
	}

	public function randomizeFloat(float $value): float {
		return $this->nextDoubleRange($this->min, $this->max);
	}

	public function getMin(): float {
		return $this->min;
	}

	public function getMax(): float {
		return $this->max;
	}

	private $min, $max;
}
