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

use encog\util\spl\types\SplEnum;

/**
 * Specifies the type of training that an object provides.
 */
class TrainingImplementationType extends SplEnum {
	/**
	 * Iterative - Each iteration attempts to improve the machine
	 * learning method.
	 */
	const Iterative = 1;

	/**
	 * Background - Training continues in the background until it is
	 * either finished or is stopped.
	 */
	const Background = 2;

	/**
	 * Single Pass - Only one iteration is necessary.
	 */
	const OnePass = 3;
}
