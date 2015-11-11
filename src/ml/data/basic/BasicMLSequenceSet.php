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
namespace encog\ml\data\basic;

use AppendIterator;
use Iterator;
use RangeException;

use encog\EncogError;
use encog\ml\data\MLData;
use encog\ml\data\MLDataError;
use encog\ml\data\MLDataPair;
use encog\ml\data\MLDataSet;
use encog\ml\data\MLSequenceSet;

/**
 * A basic implementation of the MLSequenceSet.
 */
class BasicMLSequenceSet implements MLSequenceSet {
	public static function createEmpty(): MLSequenceSet {
		$seq = new static();
		$seq->current = new BasicMLDataSet([]);
		$seq->sequences[] = $seq->current;
		return $seq;
	}

	public static function createFromArray(array $input, array $ideal = null): MLSequenceSet {
		$seq = new static();
		$seq->current = new BasicMLDataSet($input, $ideal);
		$seq->sequences[] = $seq->current;
		return $seq;
	}

	public static function createFromPairList(array $pairs): MLSequenceSet {
		$seq = new static();
		$seq->current = new BasicMLDataSet($pairs);
		$seq->sequences[] = $seq->current;
		return $seq;
	}

	public function __clone() {
		$this->sequences = array_map(function($v) { return clone $v; }, $this->sequences);
		$this->current = clone $this->current;
	}

	public function getIdealSize(): int {
		if (isset($this->sequences[0]) && $this->sequences[0]->getRecordCount()) {
			return $this->sequences[0]->getIdealSize();
		}
		return 0;
	}

	public function getInputSize(): int {
		if (isset($this->sequences[0]) && $this->sequences[0]->getRecordCount()) {
			return $this->sequences[0]->getInputSize();
		}
		return 0;
	}

	public function isSupervised(): bool {
		if (isset($this->sequences[0]) && $this->sequences[0]->getRecordCount()) {
			return $this->sequences[0]->isSupervised();
		}
		return false;
	}

	public function getRecordCount(): int {
		$result = 0;
		foreach ($this->sequences as $set) {
			$result += $set->getRecordCount();
		}
		return $result;
	}

	public function getRecord(int $index, MLDataPair $pair) {
		$recordIndex = $index;
		$sequenceIndex = 0;
		while ($this->sequences[$sequenceIndex]->getRecordCount() <= $recordIndex) {
			$recordIndex -= $this->sequences[$sequenceIndex]->getRecordCount();
			if (++$sequenceIndex >= count($this->sequences)) {
				throw new MLDataError("Record out of range: $index");
			}
		}
		$this->sequences[$sequenceIndex]->getRecord($recordIndex, $pair);
	}

	public function openAdditional(): MLDataSet {
		return clone $this;
	}

	public function add(MLData $input, MLData $ideal = null) {
		$this->current->add($input, $ideal);
	}

	public function addPair(MLDataPair $pair) {
		$this->current->addPair($pair);
	}

	public function addDataSet(MLDataSet $data) {
		foreach ($data as $pair) {
			$this->addPair($pair);
		}
	}

	public function close() { /** nothing to close */ }

	public function size(): int {
		return $this->getRecordCount();
	}

	public function get(int $index): MLDataPair {
		$pair = BasicMLDataPair::createPair($this->getInputSize(), $this->getIdealSize());
		try {
			$this->getRecord($index, $pair);
		} catch (EncogError $e) {
			throw new RangeException($e->getMessage());
		}
		return $pair;
	}

	public function startNewSequence() {
		if ($this->current->getRecordCount() > 0) {
			$this->current = new BasicMLDataSet([]);
			$this->sequences[] = $this->current;
		}
	}

	public function getSequenceCount(): int {
		return count($this->sequences);
	}

	public function getSequence(int $index): MLDataSet {
		if (!isset($this->sequences[$index])) {
			throw new RangeException();
		}
		return $this->sequences[$index];
	}

	public function getSequences(): array {
		return $this->sequences;
	}

	public function getIterator(): Iterator {
		$it = new AppendIterator();
		foreach ($this->sequences as $seq) {
			$it->append($seq->getIterator());
		}
		return $it;
	}

	private function __construct() {
	}

	/** @var MLDataSet[] */
	private $sequences = [];

	/** @var MLDataSet */
	private $current;
}
