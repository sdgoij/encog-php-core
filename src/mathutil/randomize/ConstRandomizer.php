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
namespace encog\mathutil\randomize;

/**
 * A randomizer that will create always set the random number to a const value,
 * used mainly for testing.
 */
class ConstRandomizer extends BasicRandomizer {
	public function __construct(float $value) {
		parent::__construct();
		$this->value = $value;
	}
	public function randomizeFloat(float $value): float {
		return $this->value;
	}
	private $value;
}
