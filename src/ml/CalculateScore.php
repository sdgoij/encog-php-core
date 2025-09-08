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
namespace encog\ml;

/**
 * Used by simulated annealing and genetic algorithms to calculate the score
 * for a machine learning method. This allows networks to be ranked. We may be seeking
 * a high or a low score, depending on the value the shouldMinimize
 * method returns.
 */
interface CalculateScore {
	public function calculateScore(MLMethod $method): float;
	public function shouldMinimize(): bool;
	public function requireSingleThreaded(): bool;
}
