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
namespace encog\ml\data\versatile\normalizers;

use encog\EncogError;
use encog\ml\data\MLData;
use encog\ml\data\versatile\columns\ColumnDefinition;

/**
 * A range normalizer forces a value to fall in a specific range.
 */
class RangeNormalizer implements Normalizer {
	public function __construct(float $low, float $high) {
		$this->high = $high;
		$this->low = $low;
	}

	public function outputSize(ColumnDefinition $column): int {
		return 1;
	}

	public function normalizeColumn(ColumnDefinition $column, string $value,
			array &$outputData, int $outputIndex): int {
		throw new EncogError("Can't range-normalize a string value: $value");
	}

	public function normalizeColumnDouble(ColumnDefinition $column, float $value,
			array &$outputData, int $outputIndex): int {
		$result = ($value - $column->getLow()) / ($column->getHigh() - $column->getLow())
			* ($this->high - $this->low) + $this->low;
		if (is_nan($result)) {
			$result = ($this->high-$this->low)/2 +$this->low;
		}
		$outputData[$outputIndex] = $result;
		return $outputIndex+1;
	}

	public function denormalizeColumn(ColumnDefinition $column, MLData $data, int $index): string {
		$result = ($column->getLow() - $column->getHigh()) * $data->getDataAt($index)
			- $this->high * $column->getLow()+$column->getHigh() * $this->low
			/ ($this->low - $this->high);
		if (is_nan($result)) {
			$result = $result = ($this->high-$this->low)/2 +$this->low;
		}
		return (string)$result;
	}

	private $high, $low;
}
