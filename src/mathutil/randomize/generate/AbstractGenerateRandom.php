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
namespace encog\mathutil\randomize\generate;

/**
 * Provides a foundation for most random number generation. This allows the next() method
 * to generate the other types.
 */
abstract class AbstractGenerateRandom implements GenerateRandom {
	public function nextDouble(float $high = null, float $low = 0): float {
		return $high !== null
			? $low + ($this->next() * ($high - $low))
			: $this->next();
	}

	public function nextInt(int $high = null, int $low = 0): int {
		return $high !== null
			? $low + (int)($this->next() * ($high - $low))
			: $this->nextLong();
	}

	abstract protected function next(): float;
}
