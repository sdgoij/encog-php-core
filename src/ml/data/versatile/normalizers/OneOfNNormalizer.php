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
namespace encog\ml\data\versatile\normalizers;

use encog\EncogError;
use encog\ml\data\MLData;
use encog\ml\data\versatile\columns\ColumnDefinition;

/**
 * Normalize to one-of-n for nominal values. For example, "one", "two", "three"
 * becomes 1,0,0 and 0,1,0 and 0,0,1 etc. Assuming 0 and 1 were the min/max.
 */
class OneOfNNormalizer implements Normalizer {
	public function __construct(float $low, float $high) {
		$this->high = $high;
		$this->low = $low;
	}

	public function outputSize(ColumnDefinition $column): int {
		return count($column->getClasses());
	}

	public function normalizeColumn(ColumnDefinition $column, string $value,
			array &$outputData, int $outputIndex): int {
		foreach ($column->getClasses() as $key => $class) {
			$outputData[$outputIndex+$key] = $class != $value ? $this->low : $this->high;
		}
		return $outputIndex+count($column->getClasses());
	}

	public function normalizeColumnDouble(ColumnDefinition $column, float $value,
			array &$outputData, int $outputIndex): int {
		throw new EncogError("Can't use a one-of-n normalizer on a continuous value: $value");
	}

	public function denormalizeColumn(ColumnDefinition $column, MLData $data, int $index): string {
		$bestValue = -INF;
		$bestIndex = 0;
		foreach ($data->getData() as $key => $value) {
			if ($value > $bestValue) {
				$bestValue = $value;
				$bestIndex = $key;
			}
		}
		return $column->getClasses()[$bestIndex];
	}

	private $high, $low;
}
