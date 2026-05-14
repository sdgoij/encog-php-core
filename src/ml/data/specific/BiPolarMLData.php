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
namespace encog\ml\data\specific;

use encog\mathutil\matrices\BiPolarUtil;
use encog\ml\data\MLData;
use encog\ml\data\MLDataError;
use encog\util\kmeans\Centroid;
use SplFixedArray;

/**
 * A MLData implementation designed to work with bipolar data. Bipolar data
 * contains two values. True is stored as 1, and false is stored as -1.
 */
class BiPolarMLData implements MLData {
	public function __construct($values = null) {
		if (is_int($values)) {
			$values = array_fill(0, $values, 0.0);
		}
		if (is_array($values)) {
			$this->data = BiPolarUtil::fromDoubleArray($values);
		}
	}

	public function __toString() {
		return sprintf("[%s]", join(",", array_map(function(bool $v): string {
			return $v ? "T" : "F";
		}, $this->data)));
	}

	public function add(int $index, float $value) {
		throw new MLDataError("Add is not supported for bipolar data.");
	}

	public function clear() {
		foreach ($this->data as $k => $v) $this->data[$k] = false;
	}

	public function clone(): MLData {
		return clone $this;
	}

	public function getBoolean(int $index): bool {
		return $this->data[$index];
	}

	public function getData(): SplFixedArray {
		return SplFixedArray::fromArray(BiPolarUtil::arrayToDouble($this->data));
	}

	public function getDataAt(int $index): float {
		return BiPolarUtil::toDouble($this->data[$index]);
	}

	public function setBoolean(int $index, bool $value) {
		$this->data[$index] = $value;
	}

	public function setData(SplFixedArray $values) {
		$this->data = BiPolarUtil::fromDoubleArray($values->toArray());
	}

	public function setDataAt(int $index, float $value) {
		$this->data[$index] = BiPolarUtil::fromDouble($value);
	}

	public function size(): int {
		return count($this->data);
	}

	public function createCentroid(): Centroid {
		throw new MLDataError("Not supported.");
	}

	/** @var bool[] */
	private $data = [];
}
