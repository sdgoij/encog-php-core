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

use encog\EncogError;

/**
 * Used to produce an array of activations to classify data into groups. This
 * class is provided the number of groups, as well as the range that the
 * activations should fall into.
 */
class Equilateral {
	/** The minimum number of fields to use equilateral encoding. */
	const MIN_EQ = 3;

	public function __construct(int $count, float $high, float $low) {
		$this->matrix = $this->eq($count, $high, $low);
	}

	public final function decode(array $activations): int {
		$mv = INF;
		$ms = -1;
		foreach ($this->matrix as $key => $columns) {
			$dist = $this->getDistance($activations, $key);
			if ($dist < $mv) {
				$mv = $dist;
				$ms = $key;
			}
		}
		return $ms;
	}

	public final function encode(int $set): array {
		if ($set < 0 || $set > count($this->matrix)) {
			throw new EncogError("Class out of range for equilateral: $set");
		}
		return $this->matrix[$set];
	}

	public final function getDistance(array $data, int $set): float {
		$result = 0.0;
		foreach ($data as $key => $value) {
			$result += $value-$this->matrix[$set][$key] ** 2;
		}
		return sqrt($result);
	}

	private function eq(int $n, float $high, float $low): array {
		$result[][] = -1.0;
		$result[][] = 1.0;
		$min = -1.0;
		$max = 1.0;

		for ($k = 2; $k < $n; $k++) {
			$r = $k;
			$f = sqrt($r * $r - 1.0) / $r;
			for ($i = 0; $i < $k; $i++) {
				for ($j = 0; $j < $k-1; $j++) {
					$result[$i][$j] *= $f;
				}
			}
			$r = 1.0 / $r;
			for ($i = 0; $i < $k; $i++) {
				$result[$i][$k-1] = $r;
			}
			for ($i = 0; $i < $k-1; $i++) {
				$result[$k][$i] = 0.0;
			}
			$result[$k][$k-1] = 1.0;
		}
		for ($row = 0; $row < count($result); $row++) {
			for ($col = 0; $col < count($result[0]); $col++) {
				$result[$row][$col] = ($result[$row][$col]-$min)
					/ ($max-$min) * ($high-$low) + $low;
			}
		}
		return $result;
	}

	private $matrix;
}
