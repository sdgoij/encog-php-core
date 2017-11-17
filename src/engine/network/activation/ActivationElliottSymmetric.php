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
 * Computationally efficient alternative to ActivationTANH. Its output is in
 * the range [-1, 1], and it is derivable.
 *
 * It will approach the -1 and 1 more slowly than Tanh so it might be more
 * suitable to classification tasks than predictions tasks.
 *
 * Elliott, D.L. "A better activation function for artificial neural networks", 1993
 *   http://citeseerx.ist.psu.edu/viewdoc/download?doi=10.1.1.46.7204&rep=rep1&type=pdf
 */
class ActivationElliottSymmetric extends ActivationElliott {
	public function activationFunction(SplFixedArray $values, int $start, int $size) {
		for ($i = $start, $s = $this->getParams()[0]; $i < $start+$size; $i++) {
			$values[$i] = ($values[$i]*$s) / (1+abs($values[$i]*$s));
		}
	}

	public function derivativeFunction(float $b, float $a): float {
		$s = $this->getParams()[0];
		$d = 1.0 + abs($b * $s);
		return ($s*1.0)/($d*$d);
	}

	public function getFactoryCode(): string {
		return ActivationUtil::generateActivationFactory(MLActivationFactory::ELLIOTTSYM, $this);
	}

	public function getLabel(): string {
		return "elliottsymmetric";
	}
}
