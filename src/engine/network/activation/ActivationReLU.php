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

use encog\ml\factory\MLActivationFactory;
use encog\util\obj\ActivationUtil;
use OutOfBoundsException;
use SplFixedArray;

/**
 * The Rectified Linear Unit (ReLU) computes the function f(x) = max(0,x). In other
 * words, the activation is simply thresholded at zero.
 */
class ActivationReLU implements ActivationFunction {
	const PARAM_RELU_LOW_THRESHOLD = 0;
	const PARAM_RELU_LOW = 1;

	public function __construct(float $threshold = 0.0, float $low = 0.0) {
		$this->params[self::PARAM_RELU_LOW_THRESHOLD] = $threshold;
		$this->params[self::PARAM_RELU_LOW] = $low;
	}

	public final function activationFunction(SplFixedArray $values, int $start, int $size) {
		for ($i = $start; $i < $start+$size; $i++) {
			if ($values[$i] <= $this->params[self::PARAM_RELU_LOW_THRESHOLD]) {
				$values[$i] = $this->params[self::PARAM_RELU_LOW];
			}
		}
	}

	public final function derivativeFunction(float $b, float $a): float {
		return $b <= $this->params[self::PARAM_RELU_LOW_THRESHOLD] ? 0.0 : 1.0;
	}

	public function hasDerivative(): bool {
		return true;
	}

	public function getParams(): array {
		return $this->params;
	}

	public function getParamNames(): array {
		return ["thresholdLow", "low"];
	}

	public function setParam(int $index, float $value) {
		switch ($index) {
			case self::PARAM_RELU_LOW_THRESHOLD:
			/** @noinspection PhpMissingBreakStatementInspection */
			case self::PARAM_RELU_LOW:
				$this->params[$index] = $value;
				break;
			default:
				throw new OutOfBoundsException();
		}
	}

	public function clone(): ActivationFunction {
		return clone $this;
	}

	public function getFactoryCode(): string {
		return ActivationUtil::generateActivationFactory(MLActivationFactory::RELU, $this);
	}

	public function getLabel(): string {
		return "relu";
	}

	public final function getThresholdLow(): float {
		return $this->params[self::PARAM_RELU_LOW_THRESHOLD];
	}

	public final function getLow(): float {
		return $this->params[self::PARAM_RELU_LOW];
	}

	private $params = [];
}
