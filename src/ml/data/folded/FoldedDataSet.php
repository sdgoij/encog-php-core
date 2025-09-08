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
namespace encog\ml\data\folded;

use encog\ml\data\basic\BasicMLDataPair;
use encog\ml\data\MLData;
use encog\ml\data\MLDataError;
use encog\ml\data\MLDataPair;
use encog\ml\data\MLDataSet;
use Traversable;

/**
 * A folded data set allows you to "fold" the data into several equal (or nearly
 * equal) datasets. You then have the ability to select which fold the dataset
 * will process. This is very useful for cross validation.
 *
 * This dataset works off of an underlying dataset. By default there are no
 * folds (fold size 1). Call the fold method to create more folds.
 */
class FoldedDataSet implements MLDataSet {
	/** @var MLDataSet */
	private $dataset;
	/** @var int */
	private $currentFold;
	/** @var int */
	private $numFolds;
	/** @var int */
	private $foldSize;
	/** @var int */
	private $lastFoldSize;
	/** @var int */
	private $currentFoldOffset;
	/** @var int */
	private $currentFoldSize;
	/** @var FoldedDataSet */
	private $owner;

	public function __construct(MLDataSet $dataset, int $folds = 1) {
		$this->dataset = $dataset;
		$this->fold($folds);
	}

	public function fold(int $numFolds) {
		if (!$numRecords = $this->dataset->getRecordCount()) {
			throw new MLDataError("Cannot fold empty dataset.");
		}
		$this->numFolds = (int)min($numFolds, $numRecords);
		$this->foldSize = (int)($numRecords/$this->numFolds);
		$this->lastFoldSize = (int)($this->foldSize+$numRecords);
		$this->lastFoldSize -= (int)($this->foldSize*$this->numFolds);
		$this->setCurrentFold(0);
	}

	public function getCurrentFold(): int {
		return $this->owner
			? $this->owner->getCurrentFold()
			: $this->currentFold;
	}

	public function setCurrentFold(int $index) {
		if ($this->owner) {
			throw new MLDataError("Can't set the fold on a non-top-level set.");
		}
		if ($index >= $this->numFolds) {
			throw new MLDataError("Can't set the current fold to be greater than the number of folds.");
		}
		$this->currentFold = $index;
		$this->currentFoldOffset = $this->foldSize * $this->currentFold;
		$this->currentFoldSize = $this->currentFold == $this->numFolds-1
			? $this->lastFoldSize : $this->foldSize;
	}

	public function getCurrentFoldOffset(): int {
		return $this->owner
			? $this->owner->currentFoldOffset
			: $this->currentFoldOffset;
	}

	public function getCurrentFoldSize(): int {
		return $this->owner
			? $this->owner->currentFoldSize
			: $this->currentFoldSize;
	}

	public function getIterator(): Traversable {
		for ($i = 0; $i < $this->currentFoldSize; $i++) {
			yield $this->get($i);
		}
	}

	public function getIdealSize(): int {
		return $this->dataset->getIdealSize();
	}

	public function getInputSize(): int {
		return $this->dataset->getInputSize();
	}

	public function isSupervised(): bool {
		return $this->dataset->isSupervised();
	}

	public function getRecordCount(): int {
		return $this->getCurrentFoldSize();
	}

	public function getRecord(int $index, MLDataPair $pair) {
		$this->dataset->getRecord($this->getCurrentFoldOffset()+$index, $pair);
	}

	public function openAdditional(): FoldedDataSet {
		$dataset = new static($this->dataset->openAdditional());
		$dataset->setOwner($this);
		return $dataset;
	}

	public function add(MLData $input, ?MLData $ideal = null) {
		throw new MLDataError("Direct adds to the folded dataset are not supported.");
	}

	public function addPair(MLDataPair $pair) {
		throw new MLDataError("Direct adds to the folded dataset are not supported.");
	}

	public function close() {
		$this->dataset->close();
	}

	public function size(): int {
		return $this->getRecordCount();
	}

	public function get(int $index): MLDataPair {
		$pair = BasicMLDataPair::createPair($this->getInputSize(), $this->getIdealSize());
		$this->getRecord($index, $pair);
		return $pair;
	}

	public function getDataSet(): MLDataSet {
		return $this->dataset;
	}

	public function getOwner() {
		return $this->owner;
	}

	public function setOwner(FoldedDataSet $owner) {
		$this->owner = $owner;
	}
}
