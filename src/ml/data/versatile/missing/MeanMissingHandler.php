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
namespace encog\ml\data\versatile\missing;

use encog\EncogError;
use encog\ml\data\versatile\columns\ColumnDefinition;
use encog\ml\data\versatile\NormalizationHelper;

/**
 * Handle missing data by using the mean value of that column.
 */
class MeanMissingHandler implements MissingHandler {
	public function init(NormalizationHelper $helper) {}
	public function processString(ColumnDefinition $column): string {
		throw new EncogError("The mean missing handler only accepts continuous numeric values.");
	}
	public function processDouble(ColumnDefinition $column): float {
		return $column->getMean();
	}
}
