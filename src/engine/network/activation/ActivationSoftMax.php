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

use encog\Encog;
use encog\mathutil\BoundMath;
use encog\ml\factory\MLActivationFactory;
use encog\util\obj\ActivationUtil;
use SplFixedArray;

/**
 * The softmax activation function.
 */
class ActivationSoftMax implements ActivationFunction {

	public function activationFunction(SplFixedArray $values, int $start, int $size) {
		for ($i = $start, $sum = 0; $i < $start+$size; $i++) {
			$values[$i] = BoundMath::exp($values[$i]);
			$sum += $values[$i];
		}
		if (is_nan($sum) || $sum < Encog::DEFAULT_DOUBLE_EQUAL) {
			for ($i = $start; $i < $start+$size; $i++) {
				$values[$i] = 1.0 / $size;
			}
		} else {
			for ($i = $start; $i < $start+$size; $i++) {
				$values[$i] = $values[$i] / $sum;
			}
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
		return ActivationUtil::generateActivationFactory(MLActivationFactory::SOFTMAX, $this);
	}

	public function getLabel(): string {
		return "softmax";
	}
}
