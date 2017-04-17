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
namespace encog\ml\data\versatile\normalizers\strategies;

use encog\EncogError;
use encog\ml\data\MLData;
use encog\ml\data\versatile\columns\ColumnDefinition;
use encog\ml\data\versatile\columns\ColumnType;
use encog\ml\data\versatile\normalizers\Normalizer;
use encog\ml\data\versatile\normalizers\OneOfNNormalizer;
use encog\ml\data\versatile\normalizers\RangeNormalizer;
use encog\ml\data\versatile\normalizers\RangeOrdinalNormalizer;

/**
 * Provides a basic normalization strategy that will work with most models built into Encog.
 * This is often used as a starting point for building more customized models, as this
 * normalizer works by using maps to define which normalizer to use for what data type.
 */
class BasicNormalizationStrategy implements NormalizationStrategy {
	public function __construct(float $inputLow, float $inputHigh, float $outputLow, float $outputHigh) {
		$this->assignInputNormalizer(new ColumnType(ColumnType::continuous), new RangeNormalizer($inputLow,$inputHigh));
		$this->assignInputNormalizer(new ColumnType(ColumnType::nominal), new OneOfNNormalizer($inputLow,$inputHigh));
		$this->assignInputNormalizer(new ColumnType(ColumnType::ordinal), new RangeOrdinalNormalizer($inputLow,$inputHigh));
		$this->assignOutputNormalizer(new ColumnType(ColumnType::continuous), new RangeNormalizer($outputLow,$outputHigh));
		$this->assignOutputNormalizer(new ColumnType(ColumnType::nominal), new OneOfNNormalizer($outputLow,$outputHigh));
		$this->assignOutputNormalizer(new ColumnType(ColumnType::ordinal), new RangeOrdinalNormalizer($outputLow,$outputHigh));
	}

	public function assignInputNormalizer(ColumnType $t, Normalizer $n) {
		$this->inputNormalizers[(string)$t] = $n;
	}

	public function assignOutputNormalizer(ColumnType $t, Normalizer $n) {
		$this->outputNormalizers[(string)$t] = $n;
	}

	public function getInputNormalizers(): array {
		return $this->inputNormalizers;
	}

	public function getOutputNormalizers(): array {
		return $this->outputNormalizers;
	}

	public function normalizedSize(ColumnDefinition $column, bool $isInput): int {
		return $this->findNormalizer($column, $isInput)->outputSize($column);
	}

	public function normalizeColumn(ColumnDefinition $column, bool $isInput,
			string $value, array &$outputData, int $outputColumn): int {
		return $this->findNormalizer($column, $isInput)->normalizeColumn(
			$column, $value, $outputData, $outputColumn
		);
	}

	public function normalizeColumnDouble(ColumnDefinition $column, bool $isInput,
			float $value, array &$outputData, int $outputColumn): int {
		return $this->findNormalizer($column, $isInput)->normalizeColumnDouble(
			$column, $value, $outputData, $outputColumn
		);
	}

	public function denormalizeColumn(ColumnDefinition $column, bool $isInput,
			MLData $output, int $index): string {
		return $this->findNormalizer($column, $isInput)->denormalizeColumn(
			$column, $output, $index
		);
	}

	private function findNormalizer(ColumnDefinition $column, bool $isInput): Normalizer {
		$type = (string)$column->getType();
		if ($isInput) {
			if (isset($this->inputNormalizers[$type])) {
				$norm = $this->inputNormalizers[$type];
			}
		} else {
			if (isset($this->outputNormalizers[$type])) {
				$norm = $this->outputNormalizers[$type];
			}
		}
		if (!isset($norm)) {
			throw new EncogError("No normalizer defined for input=$isInput, type=$column");
		}
		return $norm;
	}

	private $outputNormalizers = [];
	private $inputNormalizers = [];
}
