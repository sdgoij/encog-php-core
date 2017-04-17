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
namespace encog\mathutil\randomize;

use encog\mathutil\matrices\Matrix;
use encog\mathutil\randomize\generate\GenerateRandom;
use encog\ml\MLMethod;
use encog\neural\networks\BasicNetwork;

/**
 * Defines the interface for a class that is capable of randomizing the weights
 * and bias values of a neural network.
 */
interface Randomizer {
	public function getRandom(): GenerateRandom;
	public function setRandom(GenerateRandom $r);
	public function randomize(MLMethod $method);
	public function randomizeArray(array &$values, int $start = 0, int $size = null);
	public function randomizeArray2D(array &$values);
	public function randomizeFloat(float $value): float;
	public function randomizeMatrix(Matrix $m);
	public function randomizeLayer(BasicNetwork $network, int $index);
}
