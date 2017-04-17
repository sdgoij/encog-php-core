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
namespace encog\ml\data\versatile\division;

use encog\ml\data\versatile\MatrixMLDataSet;

/**
 * A division of data inside of a versatile data set.
 */
class DataDivision {
	/** @var int */
	private $count = 0;
	/** @var float */
	private $percent;
	/** @var MatrixMLDataSet */
	private $data;
	/** @var int[] */
	private $mask = [];

	public function __construct(float $percent) {
		$this->percent = $percent;
	}

	public function getCount(): int {
		return $this->count;
	}

	public function setCount(int $count) {
		$this->count = $count;
	}

	public function getPercent(): float {
		return $this->percent;
	}

	public function setPercent(float $percent) {
		$this->percent = $percent;
	}

	/** @return MatrixMLDataSet|null */
	public function getDataSet() {
		return $this->data;
	}

	public function setDataSet(MatrixMLDataSet $data) {
		$this->data = $data;
	}

	public function getMask(): array {
		return $this->mask;
	}

	public function setMask(array $mask) {
		$this->mask = $mask;
	}

	public function setMaskIndex(int $index, int $value) {
		$this->mask[$index] = $value;
	}
}
