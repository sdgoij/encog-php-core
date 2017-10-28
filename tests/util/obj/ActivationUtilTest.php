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
namespace encog\test\util\obj;

use encog\engine\network\activation\ActivationFunction;
use encog\util\obj\ActivationUtil;
use PHPUnit\Framework\TestCase;
use SplFixedArray;

class ActivationUtilTest extends TestCase {
	public function testGenerateActivationFactory() {
		$dummy1 = new ActivationDummy([1, 2, 3]);
		$dummy2 = new ActivationDummy();

		$this->assertEquals("DUMMY[1,2,3]", ActivationUtil::generateActivationFactory($dummy1->getLabel(), $dummy1));
		$this->assertEquals("DUMMY", ActivationUtil::generateActivationFactory($dummy2->getLabel(), $dummy2));
	}
}

class ActivationDummy implements ActivationFunction {
	public function __construct(array $params = []) {
		$this->params = $params;
	}

	public function activationFunction(SplFixedArray $values, int $start, int $size) {
	}

	public function derivativeFunction(float $b, float $a): float {
		return 1;
	}

	public function hasDerivative(): bool {
		return true;
	}

	public function getParams(): array {
		return $this->params;
	}

	public function getParamNames(): array {
		return [];
	}

	public function setParam(int $index, float $value) {
		$this->params[$index] = $value;
	}

	public function clone(): ActivationFunction {
		return clone $this;
	}

	public function getFactoryCode(): string {
		return "Here be dragons";
	}

	public function getLabel(): string {
		return "dummy";
	}

	private $params;
}
