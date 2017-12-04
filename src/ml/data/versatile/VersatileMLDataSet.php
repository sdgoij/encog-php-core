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
namespace encog\ml\data\versatile;

use encog\EncogError;
use encog\mathutil\randomize\generate\GenerateRandom;
use encog\ml\data\versatile\columns\ColumnDefinition;
use encog\ml\data\versatile\columns\ColumnType;
use encog\ml\data\versatile\division\PerformDataDivision;
use encog\ml\data\versatile\sources\VersatileDataSource;

/**
 * The versatile dataset supports several advanced features.
 *  1. It can directly read and normalize from a CSV file.
 *  2. It supports virtual time-boxing for time series data (the data is NOT expanded in memory).
 *  3. It can easily be segmented into smaller datasets.
 */
class VersatileMLDataSet extends MatrixMLDataSet {
	/** @var VersatileDataSource */
	private $source;
	/** @var NormalizationHelper */
	private $helper;
	/** @var int */
	private $analyzed = 0;

	public function __construct(VersatileDataSource $source) {
		$this->helper = new NormalizationHelper();
		$this->source = $source;
	}

	public function getSource(): VersatileDataSource {
		return $this->source;
	}

	public function setSource(VersatileDataSource $source) {
		$this->source = $source;
	}

	public function getHelper(): NormalizationHelper {
		return $this->helper;
	}

	public function setHelper(NormalizationHelper $helper) {
		$this->helper = $helper;
	}

	public function analyze() {
		$this->analyzed = 0;
		$this->source->rewind();
		while ($line = $this->source->readLine()) {
			/** @var ColumnDefinition $column */
			foreach ($this->helper->getSourceColumns() as $column) {
				$column->analyze($line[$this->findIndex($column)]);
			}
			$this->analyzed++;
		}
		/** @var ColumnDefinition $column */
		foreach ($this->helper->getSourceColumns() as $column) {
			if ($column->getType() == new ColumnType(ColumnType::continuous)) {
				$column->setMean($column->getMean()/$column->getCount());
				$column->setSd(0.0);
			}
		}

		$this->source->rewind();
		while ($line = $this->source->readLine()) {
			/** @var ColumnDefinition $column */
			foreach ($this->helper->getSourceColumns() as $column) {
				if ($column->getType() == new ColumnType(ColumnType::continuous)) {
					$value = $column->getMean() - $this->helper->parseDouble($line[$column->getIndex()]);
					$column->setSd($column->getSd() + $value*$value);
				}
			}
		}
		/** @var ColumnDefinition $column */
		foreach ($this->helper->getSourceColumns() as $column) {
			if ($column->getType() == new ColumnType(ColumnType::continuous)) {
				$column->setSd(sqrt($column->getSd()/$column->getCount()));
			}
		}
	}

	public function normalize() {
		if (!$strategy = $this->helper->getNormStrategy()) {
			throw new EncogError("Please choose a model type first, with selectMethod.");
		}
		$normalizedInputColumns = $this->helper->calculateNormalizedInputCount();
		$normalizedOutputColumns = $this->helper->calculateNormalizedOutputCount();
		$this->setCalculatedIdealSize($normalizedOutputColumns);
		$this->setCalculatedInputSize($normalizedInputColumns);
		$this->source->rewind();
		$empty = array_fill(0, $normalizedInputColumns+$normalizedOutputColumns, 0.0);
		$this->setData(array_fill(0, $this->analyzed, $empty));
		$row = 0;

		while ($line = $this->source->readLine()) {
			/** @var ColumnDefinition $input */
			foreach ($this->helper->getInputColumns() as $input) {
				$column = $this->helper->normalizeToVector(
					$input, $column ?? 0, $this->getData()[$row], true,
					$line[$this->findIndex($input)]
				);
			}
			/** @var ColumnDefinition $output */
			foreach ($this->helper->getOutputColumns() as $output) {
				$column = $this->helper->normalizeToVector(
					$output, $column ?? 0, $this->getData()[$row], false,
					$line[$this->findIndex($output)]
				);
			}
			$column = 0;
			$row++;
		}
	}

	public function divide(array &$divisions, bool $shuffle, GenerateRandom $random) {
		if ($this->getData() === null) {
			throw new EncogError("Can't divide, data has not yet been generated/normalized.");
		}
		$division = new PerformDataDivision($shuffle, $random);
		$division->perform($divisions, $this,
			$this->getCalculatedInputSize(),
			$this->getCalculatedIdealSize()
		);
	}

	public function defineSourceColumn(string $name, ColumnType $type, int $index = -1): ColumnDefinition {
		return $this->helper->defineSourceColumn($name, $index, $type);
	}

	public function defineOutput(ColumnDefinition $column) {
		$this->helper->getOutputColumns()[] = $column;
	}

	public function defineInput(ColumnDefinition $column) {
		$this->helper->getInputColumns()[] = $column;
	}

	public function defineSingleOutputOthersInput(ColumnDefinition $output) {
		$this->helper->clearInputOutput();
		/** @var ColumnDefinition $column */
		foreach ($this->helper->getSourceColumns() as $column) {
			if ($column == $output) {
				$this->defineOutput($column);
			} else {
				$this->defineInput($column);
			}
		}
	}

	public function defineMultipleOutputsOthersInput(array $outputs) {
		$this->helper->clearInputOutput();
		/** @var ColumnDefinition $column */
		foreach ($this->helper->getSourceColumns() as $column) {
			if (array_search($column, $outputs) !== false) {
				$this->defineOutput($column);
			} else {
				$this->defineInput($column);
			}
		}
	}

	private function findIndex(ColumnDefinition $column): int {
		if (($index = $column->getIndex()) == -1) {
			$index = $this->source->columnIndex($column->getName());
			$column->setIndex($index);
			if ($index == -1) {
				throw new EncogError("Can't find column");
			}
		}
		return $index;
	}
}
