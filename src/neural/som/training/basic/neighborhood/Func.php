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
namespace encog\neural\som\training\basic\neighborhood;

/**
 * Defines how a neighborhood function should work in competitive training. This
 * is most often used in the training process for a self-organizing map. This
 * function determines to what degree the training should take place on a
 * neuron, based on its proximity to the "winning" neuron.
 */
interface Func {
	function calculate(int $current, int $best): float;
	function getRadius(): float;
	function setRadius(float $radius);
}
