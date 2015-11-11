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

use SplFixedArray;

/**
 * This interface allows various activation functions to be used with the neural
 * network. Activation functions are applied to the output from each layer of a
 * neural network. Activation functions scale the output into the desired range.
 *
 * Methods are provided both to process the activation function, as well as the
 * derivative of the function. Some training algorithms, particularly back
 * propagation, require that it be possible to take the derivative of the
 * activation function.
 *
 * Not all activation functions support derivatives. If you implement an
 * activation function that is not derivable then an exception should be thrown
 * inside of the derivativeFunction method implementation.
 *
 * Non-derivable activation functions are perfectly valid, they simply cannot be
 * used with every training algorithm.
 */
interface ActivationFunction {
	public function activationFunction(SplFixedArray $values, int $start, int $size);
	public function derivativeFunction(float $b, float $a): float;
	public function hasDerivative(): bool;
	public function getParams(): array;
	public function getParamNames(): array;
	public function setParam(int $index, float $value);
	public function clone(): ActivationFunction;
	public function getFactoryCode(): string;
	public function getLabel(): string;
}
