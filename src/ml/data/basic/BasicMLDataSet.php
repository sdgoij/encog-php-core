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

use ArrayIterator;
use Iterator;
use encog\ml\data\MLData;
use encog\ml\data\MLDataPair;
use encog\ml\data\MLDataSet;
use RangeException;
use Traversable;

/**
 * Stores data in an array. This class is memory based, so large enough datasets
 * could cause memory issues. Many other dataset types extend this class.
 */
class BasicMLDataSet implements MLDataSet {
	public function __construct($input = null, $ideal = null) {
		if (!$ideal && is_array($input) && count($input) &&
				$input[0] instanceof MLDataPair) {
			$this->data = $input;
		} else {
			if ($ideal !== null) {
				foreach ($input as $k => $v) {
					$idealData = is_array($ideal[$k]) ? new BasicMLData($ideal[$k]) : $ideal[$k];
					$inputData = is_array($v) ? new BasicMLData($v) : $v;
					$this->add($inputData, $idealData);
				}
			} else if (is_array($input) || $input instanceof Traversable) {
				foreach ($input as $v) {
					if (!$v instanceof MLDataPair) {
						$this->add(is_array($v) ? new BasicMLData($v) : $v);
					} else {
						$this->addPair($v);
					}
				}
			}
		}
	}

	public function getData(): array {
		return $this->data;
	}

	public function setData(array $data) {
		$this->data = $data;
	}

	public function getIdealSize(): int {
		if (!count($this->data)) {
			return 0;
		}
		if (!$ideal = $this->data[0]->getIdeal()) {
			return 0;
		}
		return $ideal->size();
	}

	public function getInputSize(): int {
		if (!count($this->data)) {
			return 0;
		}
		return $this->data[0]->getInput()->size();
	}

	public function isSupervised(): bool {
		return count($this->data) && $this->data[0]->isSupervised();
	}

	public function getRecordCount(): int {
		return count($this->data);
	}

	public function getRecord(int $index, MLDataPair $pair) {
		if (!isset($this->data[$index])) throw new RangeException();
		$pair->setInputArray($this->data[$index]->getInputArray());
		if ($this->data[$index]->getIdeal()->size())
			$pair->setIdealArray($this->data[$index]->getIdealArray());
	}

	public function openAdditional(): MLDataSet {
		return clone $this;
	}

	public function add(MLData $input, MLData $ideal = null) {
		$this->addPair(new BasicMLDataPair($input, $ideal));
	}

	public function addPair(MLDataPair $pair) {
		$this->data[] = $pair;
	}

	public function close() { /** nothing to close */ }

	public function size(): int {
		return $this->getRecordCount();
	}

	public function get(int $index): MLDataPair {
		$pair = BasicMLDataPair::createPair($this->getInputSize(), $this->getIdealSize());
		$this->getRecord($index, $pair);
		return $pair;
	}

	public function getIterator(): Iterator {
		return new ArrayIterator($this->data);
	}

	/** @var MLDataPair[] */
	private $data = [];
}
