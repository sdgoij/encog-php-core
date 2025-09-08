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
namespace encog\util;

use encog\ml\data\MLDataSet;
use encog\neural\networks\ContainsFlat;
use encog\neural\NeuralNetworkError;

/**
 * Used to validate if training is valid.
 */
final class EncogValidate {
	public static function validateNetworkForTraining(ContainsFlat $network, MLDataSet $training) {
		$inputCount = $network->getFlat()->getInputCount();
		$outputCount = $network->getFlat()->getOutputCount();

		if ($inputCount != $training->getInputSize()) {
			throw new NeuralNetworkError(
				"The input layer size of $inputCount must match the " .
				"training input size of {$training->getInputSize()}."
			);
		}

		if ($training->getIdealSize() > 0 && $outputCount != $training->getIdealSize()) {
			throw new NeuralNetworkError(
				"The output layer size of $outputCount must match the " .
				"training input size of {$training->getIdealSize()}."
			);
		}
	}
	private function __construct() {}
}
