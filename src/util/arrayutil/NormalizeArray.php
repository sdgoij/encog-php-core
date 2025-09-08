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
namespace encog\util\arrayutil;

/**
 * This class is used to normalize an array. Sometimes you would like to
 * normalize an array, rather than an entire CSV file.
 */
class NormalizeArray {
	/** @var NormalizedField */
	private $field;

	public function __construct(float $low = -1.0, float $high = 1.0) {
		$this->field = new NormalizedField($high, $low);
	}

	public final function process(array $input): array {
		foreach ($input as $value) {
			$this->field->analyze($value);
		}
		foreach ($input as $value) {
			$result[] = $this->field->normalize($value);
		}
		return $result ?? [];
	}

	public final function getField(): NormalizedField {
		return $this->field;
	}

	public final function getNormalizedHigh(): float {
		return $this->field->getNormalizedHigh();
	}

	public final function setNormalizedHigh(float $normalizedHigh) {
		$this->field->setNormalizedHigh($normalizedHigh);
	}

	public final function getNormalizedLow(): float {
		return $this->field->getNormalizedLow();
	}

	public final function setNormalizedLow(float $normalizedLow) {
		$this->field->setNormalizedLow($normalizedLow);
	}
}
