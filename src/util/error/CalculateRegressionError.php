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
namespace encog\util\error;

use encog\mathutil\error\ErrorCalculation;
use encog\ml\data\MLDataPair;
use encog\ml\data\MLDataSet;
use encog\ml\MLContext;
use encog\ml\MLRegression;

final class CalculateRegressionError {
	public static function calculateError(MLRegression $method, MLDataSet $data): float {
		$error = new ErrorCalculation();

		if ($method instanceof MLContext) {
			$method->clearContext();
		}
		/** @var MLDataPair $pair */
		foreach ($data as $pair) {
			$error->updateErrorArray(
				$method->compute($pair->getInput())->getData(),
				$pair->getIdeal()->getData(),
				$pair->getSignificance()
			);
		}
		return $error->calculate();
	}
}
