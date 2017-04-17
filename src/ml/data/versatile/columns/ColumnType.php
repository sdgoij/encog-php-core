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
namespace encog\ml\data\versatile\columns;

use encog\ml\data\MLDataError;
use encog\util\spl\types\SplEnum;

/**
 * The type of column, defined using level of measurement.
 * http://en.wikipedia.org/wiki/Level_of_measurement
 */
class ColumnType extends SplEnum {
	/**
	 * A discrete nominal, or categorical, value specifies class membership. For example, US states.
	 * There is a fixed number, yet no obvious, meaningful ordering.
	 */
	const nominal = 1;

	/**
	 * A discrete ordinal specifies a non-numeric value that has a specific ordering. For example,
	 * the months of the year are inherently non-numerical, yet has a specific ordering.
	 */
	const ordinal = 2;

	/**
	 * A continuous (non-discrete) value is simply floating point numeric. These values are
	 * orderable and dense.
	 */
	const continuous = 3;

	/**
	 * This field is ignored.
	 */
	const ignore = -1;

	public function __toString() {
		if ($this == new self(self::continuous)) {
			return "continuous";
		}
		if ($this == new self(self::ordinal)) {
			return "ordinal";
		}
		if ($this == new self(self::nominal)) {
			return "nominal";
		}
		if ($this == new self(self::ignore)) {
			return "ignore";
		}
		return "unknown";
	}
}
