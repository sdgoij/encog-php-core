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

use encog\ml\factory\MLActivationFactory;
use encog\util\obj\ActivationUtil;
use SplFixedArray;

/**
 * The Steepened Sigmoid is an activation function typically used with NEAT.
 *
 * Valid derivative calculated with the R package, so this does work with
 * non-NEAT networks too.
 *
 * It was developed by  Ken Stanley while at The University of Texas at Austin.
 * http://www.cs.ucf.edu/~kstanley/
 */
class ActivationSteepenedSigmoid implements ActivationFunction {
	public function activationFunction(SplFixedArray $values, int $start, int $size) {
		for ($i = 0; $i < $start+$size; $i++) {
			$values[$i] = 1.0 / (1.0+exp(-4.9*$values[$i]));
		}
	}

	public function derivativeFunction(float $b, float $a): float {
		$s = exp(-4.9 * $a);
		return pow($s*4.9 / (1+$s), 2);
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
		return ActivationUtil::generateActivationFactory(MLActivationFactory::SSIGMOID, $this);
	}

	public function getLabel(): string {
		return "steepenedsigmoid";
	}
}
