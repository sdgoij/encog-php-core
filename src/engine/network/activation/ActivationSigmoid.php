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
namespace encog\engine\network\activation;

use encog\mathutil\BoundMath;
use encog\ml\factory\MLActivationFactory;
use encog\util\obj\ActivationUtil;
use SplFixedArray;

/**
 * The sigmoid activation function takes on a sigmoidal shape. Only positive
 * numbers are generated. Do not use this activation function if negative number
 * output is desired.
 */
class ActivationSigmoid implements ActivationFunction {
	public function activationFunction(SplFixedArray $values, int $start, int $size) {
		for ($i = $start; $i < $start+$size; $i++) {
			$values[$i] = 1 / (1 + exp(-1 * $values[$i]));
		}
	}

	public function derivativeFunction(float $b, float $a): float {
		return $a * (1.0 - $a);
	}

	public function hasDerivative(): bool {
		return true;
	}

	public function getParams(): array {
		return [];
	}

	public function getParamNames(): array {
		return [];
	}

	public function setParam(int $index, float $value) {
	}

	public function clone(): ActivationFunction {
		return clone $this;
	}

	public function getFactoryCode(): string {
		return ActivationUtil::generateActivationFactory(MLActivationFactory::SIGMOID, $this);
	}

	public function getLabel(): string {
		return "sigmoid";
	}
}
