<?php
/**
 * Copyright 2015-2016 Tim Jurcka <sdgoij@gmail.com>
 *
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
 * Computationally efficient alternative to ActivationSigmoid.
 * Its output is in the range [0, 1], and it is derivable.
 *
 * It will approach the 0 and 1 more slowly than Sigmoid so it
 * might be more suitable to classification tasks than predictions tasks.
 *
 * Elliott, D.L. "A better activation function for artificial neural networks", 1993
 *   http://citeseerx.ist.psu.edu/viewdoc/download?doi=10.1.1.46.7204&rep=rep1&type=pdf
 */
class ActivationElliott implements ActivationFunction {
	public function __construct() {
		$this->params[0] = 1.0;
	}

	public function activationFunction(SplFixedArray $values, int $start, int $size) {
		for ($i = $start; $i < $start+$size; $i++) {
			$s = $values[$i]*$this->params[0];
			$values[$i] = ($s/2) / (1+abs($s)) + 0.5;
		}
	}

	public function derivativeFunction(float $b, float $a): float {
		$s = abs($b*$this->params[0])+1.0;
		return $this->params[0] / (2.0*$s*$s);
	}

	public function hasDerivative(): bool {
		return true;
	}

	public function getParams(): array {
		return $this->params;
	}

	public function getParamNames(): array {
		return ["Slope"];
	}

	public function setParam(int $index, float $value) {
		$this->params[$index] = $value;
	}

	public function clone(): ActivationFunction {
		return clone $this;
	}

	public function getFactoryCode(): string {
		return ActivationUtil::generateActivationFactory(MLActivationFactory::ELLIOTT, $this);
	}

	public function getLabel(): string {
		return "elliott";
	}

	private $params = [];
}
