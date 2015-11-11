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
namespace encog\ml\data\auto;

use OutOfBoundsException;

class AutoFloatColumn {
	private $data = [];
	private $max;
	private $min;

	public function __construct(array $data, float $min = 0, float $max = 0) {
		$this->data = $data;
		$this->max = $max;
		$this->min = $min;

		if ($min == 0 && $max == 0) {
			$this->autoMinMax();
		}
	}

	public function __toString() {
		return sprintf("[%s:min=%f,max=%f]", __CLASS__, $this->min, $this->max);
	}

	public function autoMinMax() {
		$this->max = (float)PHP_INT_MIN;
		$this->min = (float)PHP_INT_MAX;

		foreach ($this->data as $value) {
			$this->max = max($this->max, $value);
			$this->min = min($this->min, $value);
		}
	}

	public function getData(): array {
		return $this->data;
	}

	public function getDataAt(int $index): float {
		if (!isset($this->data[$index])) {
			throw new OutOfBoundsException("$index");
		}
		return $this->data[$index];
	}

	public function getMax(): float {
		return $this->max;
	}

	public function getMin(): float {
		return $this->min;
	}

	public function getNormalized(int $index, float $min, float $max): float {
		return (($this->getDataAt($index)-$this->min)/($this->max-$this->min)) * ($max-$min) + $min;
	}
}
