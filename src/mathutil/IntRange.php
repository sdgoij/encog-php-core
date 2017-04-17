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
namespace encog\mathutil;

/**
 * A range of integers.
 */
class IntRange {
	public function __construct(int $high, int $low) {
		$this->high = $high;
		$this->low = $low;
	}

	public function getHigh(): int {
		return $this->high;
	}

	public function setHigh(int $high) {
		$this->high = $high;
	}

	public function getLow(): int {
		return $this->low;
	}

	public function setLow(int $low) {
		$this->low = $low;
	}

	private $high, $low;
}
