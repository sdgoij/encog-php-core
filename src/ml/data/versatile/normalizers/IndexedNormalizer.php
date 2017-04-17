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
 * Normalize ordinal/nominal values to a single value that is simply the index
 * of the class in the list. For example, "one", "two", "three" normalizes to
 * 0,1,2.
 */
class IndexedNormalizer implements Normalizer {
	public function outputSize(ColumnDefinition $column): int {
		return 1;
	}

	public function normalizeColumn(ColumnDefinition $column, string $value,
			array &$outputData, int $outputIndex): int {
		if (!in_array($value, $column->getClasses())) {
			throw new EncogError("Undefined value: $value");
		}
		$outputData[$outputIndex] = array_search($value, $column->getClasses());
		return $outputIndex + 1;
	}

	public function normalizeColumnDouble(ColumnDefinition $column, float $value,
			array &$outputData, int $outputIndex): int {
		throw new EncogError("Can't use an indexed normalizer on a continuous value: $value");
	}

	public function denormalizeColumn(ColumnDefinition $column, MLData $data, int $index): string {
		return $column->getClasses()[(int)$data->getDataAt($index)];
	}
}
