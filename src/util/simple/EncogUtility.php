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
namespace encog\util\simple;

use encog\engine\network\activation\ActivationSigmoid;
use encog\engine\network\activation\ActivationTANH;
use encog\mathutil\error\ErrorCalculation;
use encog\ml\data\MLDataPair;
use encog\ml\data\MLDataSet;
use encog\ml\MLContext;
use encog\ml\MLRegression;
use encog\neural\networks\BasicNetwork;
use encog\neural\pattern\FeedForwardPattern;

/**
 * General utility class for Encog. Provides for some common Encog procedures.
 */
final class EncogUtility {
	public static function simpleFeedForward(int $input, int $hidden1, int $hidden2, int $output, bool $tanh): BasicNetwork {
		$pattern = new FeedForwardPattern();
		$pattern->setInputNeurons($input);
		$pattern->setOutputNeurons($output);
		$pattern->setActivationFunction(
			$tanh ? new ActivationTANH() : new ActivationSigmoid());
		if ($hidden1 > 0) {
			$pattern->addHiddenLayer($hidden1);
		}
		if ($hidden2 > 0) {
			$pattern->addHiddenLayer($hidden2);
		}
		return $pattern->generate();
	}

	public static function calculateRegressionError(MLRegression $method, MLDataSet $data): float {
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

	private function __construct() {}
}
