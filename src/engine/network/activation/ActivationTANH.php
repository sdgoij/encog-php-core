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
namespace encog\engine\network\activation;

use encog\mathutil\BoundMath;
use encog\ml\factory\MLActivationFactory;
use encog\util\obj\ActivationUtil;
use SplFixedArray;

/**
 * The hyperbolic tangent activation function takes the curved shape of the
 * hyperbolic tangent. This activation function produces both positive and
 * negative output. Use this activation function if both negative and positive
 * output is desired.
 */
class ActivationTANH implements ActivationFunction {
	public function activationFunction(SplFixedArray $values, int $start, int $size) {
		for ($i = $start; $i < $start+$size; $i++) {
			$values[$i] = tanh($values[$i]);
		}
	}

	public function derivativeFunction(float $b, float $a): float {
		return 1.0 - $a * $a;
	}

	public function hasDerivative(): bool {
		return true;
	}

	public function getParams(): array { return []; }
	public function getParamNames(): array { return []; }
	public function setParam(int $index, float $value) {}

	public function clone(): ActivationFunction {
		return clone $this;
	}

	public function getFactoryCode(): string {
		return ActivationUtil::generateActivationFactory(MLActivationFactory::TANH, $this);
	}

	public function getLabel(): string {
		return "tanh";
	}
}
