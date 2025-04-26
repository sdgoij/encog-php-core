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
	public function nextDouble(?float $high = null, float $low = 0): float {
		return $high !== null
			? $low + $this->bound($high, $low)
			: $this->next();
	}

	public function nextInt(?int $high = null, int $low = 0): int {
		return $high !== null
			? $low + (int)round($this->bound($high, $low))
			: $this->nextLong();
	}

	public function nextBoolean(): bool { return $this->nextDouble() > 0.5; }
	public function nextFloat(): float { return $this->nextDouble(); }
	public function nextLong(): int { return $this->nextInt(); }

	/**
	 * All types are implemented in terms of next()
	 *
	 * @return float
	 */
	abstract protected function next(): float;

	private function bound($high, $low): float {
		return $this->next() * ($high - $low);
	}
}
