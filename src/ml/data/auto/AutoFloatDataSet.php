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
namespace encog\ml\data\auto;

use encog\EncogError;
use encog\ml\data\basic\BasicMLDataPair;
use encog\ml\data\MLData;
use encog\ml\data\MLDataPair;
use encog\ml\data\MLDataSet;
use encog\util\csv\CSVFormat;
use encog\util\csv\CSVReader;
use Iterator;
use Throwable;

class AutoFloatDataSet implements MLDataSet {
	/** @var int */
	private $sourceInputCount;

	/** @var int */
	private $sourceIdealCount;

	/** @var int */
	private $inputWindowSize;

	/** @var int */
	private $outputWindowSize;

	/** @var AutoFloatColumn[] */
	private $columns = [];

	/** @var float */
	private $normalizedMax = 1.0;

	/** @var float */
	private $normalizedMin = -1.0;

	/** @var bool */
	private $normalizationEnabled = false;

	public function __construct(int $inputCount, int $idealCount,
			int $inputWindowSize, int $outputWindowSize) {
		$this->sourceInputCount = $inputCount;
		$this->sourceIdealCount = $idealCount;
		$this->inputWindowSize = $inputWindowSize;
		$this->outputWindowSize = $outputWindowSize;
	}

	public function getIdealSize(): int {
		return $this->sourceIdealCount * $this->outputWindowSize;
	}

	public function getInputSize(): int {
		return $this->sourceInputCount * $this->inputWindowSize;
	}

	public function isSupervised(): bool {
		return $this->getIdealSize() > 0;
	}

	public function isNormalizationEnabled(): bool {
		return $this->normalizationEnabled;
	}

	public function setNormalizationEnabled(bool $v) {
		$this->normalizationEnabled = $v;
	}

	public function getNormalizedMax(): float {
		return $this->normalizedMax;
	}

	public function setNormalizedMax(float $v) {
		$this->normalizedMax = $v;
	}

	public function getNormalizedMin(): float {
		return $this->normalizedMin;
	}

	public function setNormalizedMin(float $v) {
		$this->normalizedMin = $v;
	}

	public function getRecordCount(): int {
		if (count($this->columns)) {
			$rows = count($this->columns[0]->getData());
			$size = $this->inputWindowSize + $this->outputWindowSize;
			return ($rows - $size) + 1;
		}
		return 0;
	}

	public function getRecord(int $index, MLDataPair $pair) {
		$input = $ideal = [];

		for ($i = 0, $columnID = 0; $i < $this->sourceInputCount; $i++, $columnID++) {
			for ($j = 0; $j < $this->inputWindowSize; $j++) {
				$input[] = $this->normalizationEnabled
					? $this->columns[$columnID]->getNormalized($index+$j, $this->normalizedMin, $this->normalizedMax)
					: $this->columns[$columnID]->getDataAt($index+$j);
			}
		}

		for ($i = 0; $i < $this->sourceIdealCount; $i++, $columnID++) {
			for ($j = 0; $j < $this->outputWindowSize; $j++) {
				$ideal[] = $this->normalizationEnabled
					? $this->columns[$columnID]->getNormalized($this->inputWindowSize+$index+$j, $this->normalizedMin, $this->normalizedMax)
					: $this->columns[$columnID]->getDataAt($this->inputWindowSize+$index+$j);
			}
		}

		$pair->setInputArray($input);
		$pair->setIdealArray($ideal);
	}

	public function openAdditional(): MLDataSet {
		return clone $this;
	}

	public function add(MLData $input, MLData $ideal = null) {
		throw new EncogError("Add's not supported by this dataset.");
	}

	public function addPair(MLDataPair $pair) {
		throw new EncogError("Add's not supported by this dataset.");
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

	public function addColumn(array $data) {
		$this->columns[] = new AutoFloatColumn($data);
	}

	public function loadCSV(string $filename, bool $headers, CSVFormat $format, array $input, array $ideal) {
		$reader = CSVReader::createFromFileName($filename, $headers, $format);
		$data = [];
		$row = 0;

		while ($reader->next()) {
			foreach (array_merge($input, $ideal) as $column => $index) {
				$data[$column][$row] = $reader->getDouble($index);
			}
			$row++;
		}
		$reader->close();

		foreach ($data as $column) {
			$this->addColumn($column);
		}
	}

	public function getIterator(): Iterator {
		return new class($this) implements Iterator {
			public function __construct(MLDataSet $subject) {
				$this->subject = $subject;
				$this->index = 0;
			}

			public function current() {
				return $this->subject->get($this->index);
			}

			public function next() {
				$this->index++;
			}

			public function key() {
				return $this->index;
			}

			public function valid() {
				try {
					$this->subject->get($this->index);
				} catch (Throwable $e) {
					return false;
				}
				return true;
			}

			public function rewind() {
				$this->index = 0;
			}

			/** @var MLDataSet */
			private $subject;

			/** @var int */
			private $index;
		};
	}
}
