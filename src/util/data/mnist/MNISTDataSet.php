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
namespace encog\util\data\mnist;

use encog\ml\data\basic\BasicMLDataPair;
use encog\ml\data\MLData;
use encog\ml\data\MLDataError;
use encog\ml\data\MLDataPair;
use encog\ml\data\MLDataSet;
use encog\util\arrayutil\NormalizeArray;
use Iterator;
use Throwable;

class MNISTDataSet implements MLDataSet {
	public function __construct(MNISTReader $reader) {
		$this->normalizer = new NormalizeArray(0.0, 1.0);
		$this->reader = $reader;
	}

	public function __clone() {
		$this->normalizer = clone $this->normalizer;
		$this->reader = clone $this->reader;
	}

	public function getIterator(): Iterator {
		for ($i = 0, $m = count($this->reader); $i < $m; $i++) {
			try {
				yield $this->get($i);
			} catch (Throwable $e) {
				return;
			}
		}
	}

	public function getIdealSize(): int {
		return 10;
	}

	public function getInputSize(): int {
		return $this->reader->getImageRecordSize();
	}

	public function isSupervised(): bool {
		return true;
	}

	public function getRecordCount(): int {
		return count($this->reader);
	}

	public function getRecord(int $index, MLDataPair $pair) {
		$this->reader->seek($index);
		$record = $this->reader->current();
		$pair->setInputArray($this->normalizer->process($record[0]));
		$pair->setIdealArray(array_fill(0, $this->getIdealSize(), 0.0));
		$pair->getIdeal()->setDataAt($record[1], 1.0);
	}

	public function openAdditional(): MLDataSet {
		return clone $this;
	}

	public function add(MLData $input, ?MLData $ideal = null) {
		throw new MLDataError("Direct adds to the MNIST dataset are not supported.");
	}

	public function addPair(MLDataPair $pair) {
		throw new MLDataError("Direct adds to the MNIST dataset are not supported.");
	}

	public function close() {}

	public function size(): int {
		return $this->getRecordCount();
	}

	public function get(int $index): MLDataPair {
		$pair = BasicMLDataPair::createPair($this->getInputSize(), $this->getIdealSize());
		$this->getRecord($index, $pair);
		return $pair;
	}

	/** @var NormalizeArray */
	private $normalizer;

	/** @var MNISTReader */
	private $reader;
}
