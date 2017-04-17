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
 * Normalize an ordinal into a specific range. An ordinal is a string value that
 * has order. For example "first grade", "second grade", ... "freshman", ...,
 * "senior". These values are mapped to an increasing index.
 */
class RangeOrdinalNormalizer implements Normalizer {
	public function __construct(float $low, float $high) {
		$this->high = $high;
		$this->low = $low;
	}

	public function outputSize(ColumnDefinition $column): int {
		return 1;
	}

	public function normalizeColumn(ColumnDefinition $column, string $value,
			array &$outputData, int $outputIndex): int {
		if (($index = array_search($value, $column->getClasses())) === false) {
			throw new EncogError("Unknown ordinal: $value");
		}
		$high = count($column->getClasses());
		$result = ($index/$high) * ($this->high-$this->low) + $this->low;
		if (is_nan($result)) {
			$result = ($this->high-$this->low)/2 + $this->low;
		}
		$outputData[$outputIndex] = $result;
		return $outputIndex+1;
	}

	public function normalizeColumnDouble(ColumnDefinition $column, float $value,
			array &$outputData, int $outputIndex): int {
		throw new EncogError("Can't ordinal range-normalize a continuous value: $value");
	}

	public function denormalizeColumn(ColumnDefinition $column, MLData $data, int $index): string {
		$high = count($column->getClasses());
		$low = 0;
		$value = $data->getDataAt($index);
		$result = (($low-$high) * $value - $this->high * $low+$high
			* $this->low) / ($this->low-$this->high);
		if (is_nan($result)) $result = 0;
		return $column->getClasses()[(int)$result];
	}

	private $high, $low;
}
