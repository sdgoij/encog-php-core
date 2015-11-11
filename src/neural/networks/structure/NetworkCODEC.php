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
namespace encog\neural\networks\structure;

use encog\ml\MLEncodable;
use encog\ml\MLMethod;
use encog\neural\NeuralNetworkError;

/**
 * This class will extract the "long term memory" of a neural network, that is
 * the weights and bias values into an array. This array can be used to view the
 * neural network as a linear array of doubles. These values can then be
 * modified and copied back into the neural network. This is very useful for
 * simulated annealing, as well as genetic algorithms.
 */
final class NetworkCODEC {
	public static function networkSize(MLMethod $method): int {
		if (!$method instanceof MLEncodable) {
			throw new NeuralNetworkError("This machine learning method cannot be encoded: "
				. get_class($method));
		}
		return $method->encodedArrayLength();
	}

	public static function arrayToNetwork(array $weights, MLMethod $method) {
		if (!$method instanceof MLEncodable) {
			throw new NeuralNetworkError("This machine learning method cannot be encoded: "
				. get_class($method));
		}
		return $method->decodeFromArray($weights);
	}

	public static function networkToArray(MLMethod $method): array {
		if (!$method instanceof MLEncodable) {
			throw new NeuralNetworkError("This machine learning method cannot be encoded: "
				. get_class($method));
		}
		$values = [];
		$method->encodeToArray($values);
		return $values;
	}

	private function __construct() {
	}
}
