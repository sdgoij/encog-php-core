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
namespace encog\ml\data\basic;

use Countable;
use InvalidArgumentException;

use encog\ml\data\MLData;
use encog\util\kmeans\Centroid;
use RangeException;
use SplFixedArray;

/**
 * Basic implementation of the MLData interface that stores the data in an array.
 */
class BasicMLData implements Countable, MLData {
	public function __construct($data = null) {
		if (is_int($data)) {
			$data = new SplFixedArray($data);
		}
		if (is_array($data)) {
			$data = SplFixedArray::fromArray($data);
		}
		if ($data) $this->data = $data;
	}

	public function __clone() {
		$this->data = clone $this->data;
	}

	public function __toString() {
		return sprintf("[%s:%s]", __CLASS__, join(",", $this->data->toArray()));
	}

	public static function copy(MLData $data): MLData {
		return new static($data->getData());
	}

	public function count(): int {
		return $this->size();
	}

	public function createCentroid(): Centroid {
		return new BasicMLDataCentroid($this);
	}

	public function add(int $index, float $value) {
		if (!isset($this->data[$index])) {
			throw new RangeException("Index '$index' out of bounds.");
		}
		$this->data[$index] += $value;
	}

	public function clear() {
		foreach ($this->data as $k => $v) {
			$this->data[$k] = 0.0;
		}
	}

	public function clone(): MLData {
		return clone $this;
	}

	public function getData(): SplFixedArray {
		return $this->data;
	}

	public function getDataAt(int $index): float {
		return $this->data[$index];
	}

	public function setData(SplFixedArray $values) {
		$this->data = $values;
	}

	public function setDataAt(int $index, float $value) {
		$this->data[$index] = $value;
	}

	public function size(): int {
		return count($this->data);
	}

	/** @var SplFixedArray */
	private $data;

	public function plus(MLData $o): MLData {
		if ($this->size() != $o->size()) {
			throw new InvalidArgumentException();
		}
		$result = $this->clone();
		foreach ($o->getData() as $k => $v) {
			$result->setDataAt($k, $this->data[$k]+$v);
		}
		return $result;
	}

	public function minus(MLData $o): MLData {
		if ($this->size() != $o->size()) {
			throw new InvalidArgumentException();
		}
		$result = $this->clone();
		foreach ($o->getData() as $k => $v) {
			$result->setDataAt($k, $this->data[$k]-$v);
		}
		return $result;
	}

	public function times(float $v): MLData {
		$result = $this->clone();
		foreach ($result->getData() as $k => $o) {
			$result->setDataAt($k, $o*$v);
		}
		return $result;
	}

	public function threshold(float $value, float $low, float $high): MLData {
		$result = new static($this->size());
		$size = $this->size();
		for ($i = 0; $i < $size; $i++) {
			if ($this->getDataAt($i) > $value) {
				$result->setDataAt($i, $high);
			} else {
				$result->setDataAt($i, $low);
			}
		}
		return $result;
	}
}
