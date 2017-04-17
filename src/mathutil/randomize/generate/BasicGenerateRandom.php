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

use encog\util\Random;

/**
 * A wrapper over encog\util\Random random number generator.
 */
class BasicGenerateRandom extends AbstractGenerateRandom {
	public function __construct(int $seed = null) {
		$this->random = new Random($seed);
	}

	public function nextGaussian(): float {
		return $this->random->nextGaussian();
	}

	public function nextBoolean(): bool {
		return $this->random->nextBoolean();
	}

	public function nextLong(): int {
		return $this->random->nextLong();
	}

	public function nextFloat(): float {
		return $this->random->nextFloat();
	}

	protected function next(): float {
		return $this->random->nextDouble();
	}

	/** @var Random */
	private $random;
}
