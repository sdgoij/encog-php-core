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
use encog\ml\data\basic\BasicMLData;
use encog\ml\data\MLData;
use encog\ml\data\versatile\columns\ColumnDefinition;
use encog\ml\data\versatile\columns\ColumnType;
use encog\ml\data\versatile\missing\MissingHandler;
use encog\ml\data\versatile\normalizers\strategies\NormalizationStrategy;
use encog\util\csv\CSVFormat;
use SplFixedArray;

/**
 * This class is used to perform normalizations for methods
 * trained with the VersatileDataSet.
 */
class NormalizationHelper {
	/** @var ColumnDefinition[] */
	private $sourceColumns = [];
	/** @var ColumnDefinition[] */
	private $inputColumns = [];
	/** @var ColumnDefinition[] */
	private $outputColumns = [];
	/** @var NormalizationStrategy */
	private $normStrategy;
	/** @var CSVFormat */
	private $format;
	/** @var string[] */
	private $unknownValues = [];
	/** @var MissingHandler[] */
	private $missingHandlers = [];

	public function &getSourceColumns(): array {
		return $this->sourceColumns;
	}

	public function setSourceColumns(array $sourceColumns) {
		$this->sourceColumns = $sourceColumns;
	}

	public function &getInputColumns(): array {
		return $this->inputColumns;
	}

	public function setInputColumns(array $inputColumns) {
		$this->inputColumns = $inputColumns;
	}

	public function &getOutputColumns(): array {
		return $this->outputColumns;
	}

	public function setOutputColumns(array $outputColumns) {
		$this->outputColumns = $outputColumns;
	}

	public function getNormStrategy() {
		return $this->normStrategy;
	}

	public function setNormStrategy(NormalizationStrategy $normStrategy) {
		$this->normStrategy = $normStrategy;
	}

	public function getUnknownValues(): array {
		return $this->unknownValues;
	}

	public function defineUnknownValue(string $value) {
		$this->unknownValues[] = $value;
	}

	public function setUnknownValues(array $unknownValues) {
		$this->unknownValues = $unknownValues;
	}

	public function getFormat(): CSVFormat {
		return $this->format ?? CSVFormat::$decimalPoint;
	}

	public function setFormat(CSVFormat $format) {
		$this->format = $format;
	}

	public function getMissingHandlers(): array {
		return $this->missingHandlers;
	}

	public function defineMissingHandler(ColumnDefinition $column, MissingHandler $handler) {
		$this->missingHandlers[(string)$column] = $handler;
		$handler->init($this);
	}

	public function setMissingHandlers(array $missingHandlers) {
		$this->missingHandlers = $missingHandlers;
	}

	public function addSourceColumn(ColumnDefinition $column) {
		$this->sourceColumns[] = $column;
		$column->setOwner($this);
	}

	public function defineSourceColumn(string $name, int $index, ColumnType $type): ColumnDefinition {
		$column = new ColumnDefinition($name, $type);
		$column->setIndex($index);
		$this->addSourceColumn($column);
		return $column;
	}

	public function clearInputOutput() {
		$this->inputColumns = [];
		$this->outputColumns = [];
	}

	public function normalizeInputColumn(int $index, string $value): array {
		$result = [];
		$column = $this->inputColumns[$index];
		$this->normStrategy->normalizeColumn($column, true, $value, $result, 0);
		return $result;
	}

	public function normalizeOutputColumn(int $index, string $value): array {
		$result = [];
		$column = $this->outputColumns[$index];
		$this->normStrategy->normalizeColumn($column, true, $value, $result, 0);
		return $result;
	}

	public function calculateNormalizedInputCount(): int {
		$total = 0;
		foreach ($this->inputColumns as $column) {
			$total += $this->normStrategy->normalizedSize($column, true);
		}
		return $total;
	}

	public function calculateNormalizedOutputCount(): int {
		$total = 0;
		foreach ($this->outputColumns as $column) {
			$total += $this->normStrategy->normalizedSize($column, false);
		}
		return $total;
	}

	public function allocateInputVector($multiplier = 1): BasicMLData {
		return new BasicMLData(new SplFixedArray($this->calculateNormalizedInputCount() * $multiplier));
	}

	public function denormalizeOutputVectorToString(MLData $output): array {
		$result = [];
		$index = 0;

		foreach ($this->outputColumns as $column) {
			$result[] = $this->normStrategy->denormalizeColumn($column, false, $output, $index);
			$index += $this->normStrategy->normalizedSize($column, false);
		}
		return $result;
	}

	public function parseDouble(string $value): float {
		return $this->getFormat()->parse($value);
	}

	public function normalizeToVector(ColumnDefinition $column, int $outputColumn,
			array &$output, bool $isInput, string $value): int {
		if (in_array($value, $this->unknownValues)) {
			if (!isset($this->missingHandlers[(string)$column])) {
				throw new EncogError("Do not know how to process missing value \"$value\" in field: {$column->getName()}");
			}
			/** @var MissingHandler $handler */
			$handler = $this->missingHandlers[(string)$column];
		}
		if ($column->getType() == new ColumnType(ColumnType::continuous)) {
			$value = isset($handler)
				? $handler->processDouble($column)
				: $this->parseDouble($value);
			return $this->normStrategy->normalizeColumnDouble(
				$column, $isInput, $value, $output, $outputColumn
			);
		}
		if (isset($handler)) {
			$value = $handler->processString($column);
		}
		return $this->normStrategy->normalizeColumn(
				$column, $isInput, $value, $output, $outputColumn
		);
	}

	public function normalizeInputVector(array $lines, array &$data, bool $originalOrder) {
		foreach ($this->inputColumns as $key => $column) {
			$outputIndex = self::normalizeToVector($column, $outputIndex ?? 0, $data, true, $lines[
				$originalOrder ? array_search($column, $this->sourceColumns) : $key
			]);
		}
	}
}
