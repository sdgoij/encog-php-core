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
namespace encog\ml\data\versatile\normalizers;

use encog\ml\data\MLData;
use encog\ml\data\versatile\columns\ColumnDefinition;

/**
 * The normalizer interface defines how to normalize a column.  The source of the
 * normalization can be either string or double.
 */
interface Normalizer {
	public function outputSize(ColumnDefinition $column): int;
	public function normalizeColumn(ColumnDefinition $column, string $value,
		array &$outputData, int $outputIndex): int;
	public function normalizeColumnDouble(ColumnDefinition $column, float $value,
		array &$outputData, int $outputIndex): int;
	public function denormalizeColumn(ColumnDefinition $column, MLData $data, int $index): string;
}
