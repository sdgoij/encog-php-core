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
namespace encog\neural\error;

use encog\engine\network\activation\ActivationFunction;
use SplFixedArray;

/**
 * An error function. This is used to calculate the errors for the
 * output layer during propagation training.
 */
interface ErrorFunction {
	public function calculateError(
		ActivationFunction $af,
		array $b,
		array $a,
		array $ideal,
		array $actual,
		SplFixedArray $error,
		float $derivShift,
		float $significance
	);
}
