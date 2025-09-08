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
namespace encog\ml\data\versatile;

use encog\EncogError;
use encog\ml\data\basic\BasicMLDataPair;
use encog\ml\data\MLData;
use encog\ml\data\MLDataPair;
use encog\ml\data\MLDataSet;
use RangeException;
use Traversable;

/**
 * The MatrixMLDataSet can use a large 2D matrix of doubles to internally hold
 * data. It supports several advanced features such as the ability to mask and
 * time-box. Masking allows several datasets to use the same backing array,
 * however use different parts.
 *
 * Time boxing allows time-series data to be represented for prediction. The
 * following shows how data is laid out for different lag and lead settings.
 *
 *   Lag 0; Lead 0 [10 rows] 1→1 2→2 3→3 4→4 5→5 6→6 7→7 8→8 9→9 10→10
 *   Lag 0; Lead 1 [9 rows] 1→2 2→3 3→4 4→5 5→6 6→7 7→8 8→9 9→10
 *   Lag 1; Lead 0 [9 rows, not useful] 1,2→1 2,3→2 3,4→3 4,5→4 5,6→5 6,7→6 7,8→7 8,9→8 9,10→9
 *   Lag 1; Lead 1 [8 rows] 1,2→3 2,3→4 3,4→5 4,5→6 5,6→7 6,7→8 7,8→9 8,9→10
 *   Lag 1; Lead 2 [7 rows] 1,2→3,4 2,3→4,5 3,4→5,6 4,5→6,7 5,6→7,8 6,7→8,9 7,8→9,10
 *   Lag 2; Lead 1 [7 rows] 1,2,3→4 2,3,4→5 3,4,5→6 4,5,6→7 5,6,7→8 6,7,8→9 7,8,9→10
 */
class MatrixMLDataSet implements MLDataSet {
	private $calculatedInputSize = -1;
	private $calculatedIdealSize = -1;
	private $lagWindowSize = 0;
	private $leadWindowSize = 0;
	private $data = null;
	private $mask = null;

	public static function createFromArray(array $data, int $input, int $ideal, ?array $mask = null): MatrixMLDataSet {
		$dataset = new static();
		$dataset->calculatedInputSize = $input;
		$dataset->calculatedIdealSize = $ideal;
		$dataset->data = $data;
		$dataset->mask = $mask;
		return $dataset;
	}

	public static function createFromMatrixMLDataSet(MatrixMLDataSet $data, ?array $mask = null): MatrixMLDataSet {
		$dataset = new static();
		$dataset->calculatedInputSize = $data->getCalculatedInputSize();
		$dataset->calculatedIdealSize = $data->getCalculatedIdealSize();
		$dataset->data = $data->getData();
		$dataset->mask = $mask;
		return $dataset;
	}

	public function getIterator(): Traversable {
		for ($i = 0; ; $i++) {
			try {
				yield $this->get($i);
			} catch (RangeException $e) {
				break;
			}
		}
	}

	public function getIdealSize(): int {
		return $this->calculatedIdealSize * min($this->leadWindowSize, 1);
	}

	public function getInputSize(): int {
		return $this->calculatedIdealSize * $this->leadWindowSize;
	}

	public function isSupervised(): bool {
		return $this->getIdealSize() == 0;
	}

	public function getRecordCount(): int {
		if ($this->data === null) {
			throw new EncogError("DataSet must be normalized before using.");
		}
		if ($this->mask === null) {
			return count($this->data) - ($this->lagWindowSize + $this->leadWindowSize);
		}
		return count($this->mask) - ($this->lagWindowSize + $this->leadWindowSize);
	}

	public function getRecord(int $index, MLDataPair $pair) {
		if ($this->data === null) {
			throw new EncogError("DataSet must be normalized before using.");
		}
		$inputSize = $this->calculateLagCount();
		for ($i = 0; $i < $inputSize; $i++) {
			self::copyToPairInput(
				$this->lookupDataRow($index+$i),
				0,
				$pair,
				$i*$this->calculatedInputSize,
				$this->calculatedInputSize
			);
		}
		$outputStart = $this->leadWindowSize > 0 ? 1 : 0;
		$outputSize = $this->calculateLeadCount();
		for ($i = 0; $i < $outputSize; $i++) {
			self::copyToPairIdeal(
				$this->lookupDataRow($index+$i+$outputStart),
				$this->calculatedIdealSize,
				$pair,
				$i*$this->calculatedIdealSize,
				$this->calculatedIdealSize
			);
		}
	}

	public function openAdditional(): MLDataSet {
		$ds = self::createFromMatrixMLDataSet($this, $this->mask);
		$ds->setLagWindowSize($this->getLagWindowSize());
		$ds->setLeadWindowSize($this->getLeadWindowSize());
		return $ds;
	}

	public function add(MLData $input, ?MLData $ideal = null) {}
	public function addPair(MLDataPair $pair) {}
	public function close() {}

	public function size(): int {
		return $this->getRecordCount();
	}

	public function get(int $index): MLDataPair {
		$pair = BasicMLDataPair::createPair(
			$this->calculatedInputSize * $this->calculateLagCount(),
			$this->calculatedIdealSize * $this->calculateLeadCount()
		);
		$this->getRecord($index, $pair);
		return $pair;
	}

	public function getCalculatedInputSize(): int {
		return $this->calculatedInputSize;
	}

	public function setCalculatedInputSize(int $calculatedInputSize) {
		$this->calculatedInputSize = $calculatedInputSize;
	}

	public function getCalculatedIdealSize(): int {
		return $this->calculatedIdealSize;
	}

	public function setCalculatedIdealSize(int $calculatedIdealSize) {
		$this->calculatedIdealSize = $calculatedIdealSize;
	}

	public function &getData() {
		return $this->data;
	}

	public function setData(array $data) {
		$this->data = $data;
	}

	public function getMask(): array {
		return $this->mask ?? [];
	}

	public function setMask(array $mask) {
		$this->mask = $mask;
	}

	public function getLagWindowSize(): int {
		return $this->lagWindowSize;
	}

	public function setLagWindowSize(int $lagWindowSize) {
		$this->lagWindowSize = $lagWindowSize;
	}

	public function getLeadWindowSize(): int {
		return $this->leadWindowSize;
	}

	public function setLeadWindowSize(int $leadWindowSize) {
		$this->leadWindowSize = $leadWindowSize;
	}

	private function calculateLagCount(): int {
		return $this->lagWindowSize <= 0 ? 1 : $this->lagWindowSize+1;
	}

	private function calculateLeadCount(): int {
		return $this->leadWindowSize <= 1 ? 1 : $this->leadWindowSize;
	}

	private function lookupDataRow(int $index): array {
		if (!isset($this->data[$index]) && !isset($this->data[$this->mask[$index] ?? null])) {
			throw new RangeException("Index '$index' out of bounds.");
		}
		if ($this->mask) {
			return $this->data[$this->mask[$index]];
		}
		return $this->data[$index];
	}

	private static function copyToPairInput(array $source, int $start, MLDataPair $pair, int $offset, int $length) {
		for ($i = 0; $i < $length; $i++) $pair->getInput()->getData()[$offset+$i] = $source[$start+$i];
	}

	private static function copyToPairIdeal(array $source, int $start, MLDataPair $pair, int $offset, int $length) {
		for ($i = 0; $i < $length; $i++) $pair->getIdeal()->getData()[$offset+$i] = $source[$start+$i];
	}
}
