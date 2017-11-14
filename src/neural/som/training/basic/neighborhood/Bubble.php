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
namespace encog\neural\som\training\basic\neighborhood;

/**
 * A neighborhood function that uses a simple bubble. A radius is defined, and
 * any neuron that is plus or minus that width from the winning neuron will be
 * updated as a result of training.
 */
class Bubble implements Func {
	public function __construct(float $radius) {
		$this->radius = $radius;
	}

	public function calculate(int $current, int $best): float {
		return abs($best-$current) <= $this->radius ? 1.0 : 0.0;
	}

	public function getRadius(): float {
		return $this->radius;
	}

	public function setRadius(float $radius): void {
		$this->radius = $radius;
	}

	/** @var float */
	private $radius;
}
