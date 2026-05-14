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
use SplFixedArray;

/**
 * The Linear layer is really not an activation function at all. The input is
 * simply passed on, unmodified, to the output. This activation function is
 * primarily theoretical and of little actual use. Usually an activation
 * function that scales between 0 and 1 or -1 and 1 should be used.
 */
class ActivationLinear implements ActivationFunction {
    final public function __construct() {}
	public function activationFunction(SplFixedArray $values, int $start, int $size) {}
	public function derivativeFunction(float $b, float $a): float { return 1.0; }
	public function hasDerivative(): bool { return true; }
	public function getParams(): array { return []; }
	public function getParamNames(): array { return []; }
	public function setParam(int $index, float $value) {}
	public function clone(): ActivationFunction { return new static(); }
	public function getFactoryCode(): string {
		return ActivationUtil::generateActivationFactory(MLActivationFactory::LINEAR, $this);
	}
	public function getLabel(): string { return "linear"; }
}
