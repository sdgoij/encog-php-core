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

require_once __DIR__ . "/NormalizedField.php";

/**
 * This class is used to normalize an array. Sometimes you would like to
 * normalize an array, rather than an entire CSV file.
 */
class NormalizeArray {
	/** @var NormalizedField */
	private $field;
	/** @var float */
	private $normalizedHigh;
	/** @var float */
	private $normalizedLow;

	public function __construct(float $low = -1.0, float $high = 1.0) {
		$this->normalizedHigh = $high;
		$this->normalizedLow = $low;
	}

	public final function process(array $input): array {
		$this->field = new NormalizedField($this->normalizedHigh, $this->normalizedLow);
		$result = [];
		foreach ($input as $value) {
			$this->field->analyze($value);
		}
		foreach ($input as $value) {
			$result[] = $this->field->normalize($value);
		}
		return $result;
	}

	public final function getField() {
		return $this->field;
	}

	public final function getNormalizedHigh(): float {
		return $this->normalizedHigh;
	}

	public final function setNormalizedHigh(float $normalizedHigh) {
		$this->normalizedHigh = $normalizedHigh;
	}

	public final function getNormalizedLow(): float {
		return $this->normalizedLow;
	}

	public final function setNormalizedLow(float $normalizedLow) {
		$this->normalizedLow = $normalizedLow;
	}
}
