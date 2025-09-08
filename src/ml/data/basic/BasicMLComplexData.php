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
namespace encog\ml\data\basic;

use InvalidArgumentException;
use RangeException;

use encog\mathutil\ComplexNumber;
use encog\ml\data\MLComplexData;
use encog\ml\data\MLData;
use encog\util\kmeans\Centroid;
use SplFixedArray;

/**
 * This class implements a data object that can hold complex numbers. It
 * implements the interface MLData, so it can be used with nearly any Encog
 * machine learning method. However, not all Encog machine learning methods
 * are designed to work with complex numbers. A Encog machine learning method
 * that does not support complex numbers will only be dealing with the
 * real-number portion of the complex number.
 */
class BasicMLComplexData implements MLComplexData {
	public function __construct($data) {
		if (is_array($data) || $data instanceof SplFixedArray && count($data)) {
			foreach ($data as $k => $v) {
				if (!$v instanceof ComplexNumber) {
					$data[$k] = new ComplexNumber($v, 0);
				}
			}
			if (is_array($data)) {
				$data = SplFixedArray::fromArray($data);
			}
		}
		if (is_int($data)) {
			$data = new SplFixedArray($data);
		}
		if (!$data instanceof SplFixedArray) {
			throw new InvalidArgumentException();
		}
		$this->data = $data;
	}

	public function __clone() {
		$this->data = clone $this->data;
	}

	public static function createFromMLData(MLData $data): MLComplexData {
		if (!$data instanceof MLComplexData) {
			return new static($data->getData());
		}
		return clone $data;
	}

	public function addComplex(int $index, ComplexNumber $value) {
		if (!isset($this->data[$index])) {
			throw new RangeException();
		}
		$this->data[$index] = $this->data[$index]->plus($value);
	}

	public function getComplexData(): SplFixedArray {
		return $this->data;
	}

	public function getComplexDataAt(int $index): ComplexNumber {
		if (!isset($this->data[$index])) {
			throw new RangeException();
		}
		return $this->data[$index];
	}

	public function setComplexData(SplFixedArray $data) {
		foreach ($data as $value) {
			if (!$value instanceof ComplexNumber) {
				throw new InvalidArgumentException("All values must be of type ComplexNumber.");
			}
		}
		$this->data = $data;
	}

	public function setComplexDataAt(int $index, ComplexNumber $data) {
		$this->data[$index] = $data;
	}

	public function add(int $index, float $value) {
		$this->addComplex($index, new ComplexNumber($value, 0));
	}

	public function clear() {
		foreach ($this->data as $k => $v) {
			$this->data[$k] = new ComplexNumber(0, 0);
		}
	}

	public function clone(): MLData {
		return clone $this;
	}

	public function getData(): SplFixedArray {
		$values = new SplFixedArray($this->size());
		foreach ($this->data as $key => $complexNumber) {
			$values[$key] = $complexNumber->getReal();
		}
		return $values;
	}

	public function getDataAt(int $index): float {
		return $this->getComplexDataAt($index)->getReal();
	}

	public function setData(SplFixedArray $values) {
		foreach ($values as $k => $v) {
			if (!$v instanceof ComplexNumber) {
				$values[$k] = new ComplexNumber($v, 0);
			}
		}
		$this->setComplexData($values);
	}

	public function setDataAt(int $index, float $value) {
		$this->setComplexDataAt($index, new ComplexNumber($value, 0));
	}

	public function size(): int {
		return count($this->data);
	}

	public function createCentroid(): Centroid {
		return new class implements Centroid {
			public function add($element) {}
			public function remove($element) {}
			public function distance($element): float {
				return 0.0;
			}
		};
	}

	///** @var ComplexNumber[] */
	private $data;
}
