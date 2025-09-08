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
namespace encog\ml\data\versatile\normalizers\strategies;

use encog\ml\data\MLData;
use encog\ml\data\versatile\columns\ColumnDefinition;

/**
 * Defines the interface to a normalization strategy.
 */
interface NormalizationStrategy {
	public function normalizedSize(ColumnDefinition $column, bool $isInput): int;
	public function normalizeColumn(ColumnDefinition $column, bool $isInput,
		string $value, array &$outputData, int $outputColumn): int;
	public function normalizeColumnDouble(ColumnDefinition $column, bool $isInput,
			float $value, array &$outputData, int $outputColumn): int;
	public function denormalizeColumn(ColumnDefinition $column, bool $isInput,
		MLData $output, int $index): string;
}
