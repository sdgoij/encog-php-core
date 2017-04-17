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
namespace encog\util\arrayutil;

use encog\util\spl\types\SplEnum;

/**
 * Normalization actions desired.
 */
class NormalizationAction extends SplEnum {
	/**
	 * Do not normalize the column, just allow it to pass through. This allows
	 * string fields to pass through as well.
	 */
	const PassThrough = 1;

	/**
	 * Normalize this column.
	 */
	const Normalize = 2;

	/**
	 * Ignore this column, do not include in the output.
	 */
	const Ignore = 3;

	/**
	 * Use the "one-of" classification method.
	 */
	const OneOf = 4;

	/**
	 * Use the equilateral classification method.
	 */
	const Equilateral = 5;

	/**
	 * Use a single-field classification method.
	 */
	const SingleField = 6;

	public function isClassify(): bool {
		return $this == new self(self::OneOf)
				|| $this == new self(self::SingleField)
				|| $this == new self(self::Equilateral)
			;
	}
}
