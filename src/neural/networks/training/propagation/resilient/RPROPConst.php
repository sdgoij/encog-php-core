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
namespace encog\neural\networks\training\propagation\resilient;

/**
 * Constants used for Resilient Propagation (RPROP) training.
 */
final class RPROPConst {
	/**
	 * The default zero tolerance.
	 */
	const DEFAULT_ZERO_TOLERANCE = 0.00000000000000001;
	/**
	 * The POSITIVE ETA value. This is specified by the resilient propagation
	 * algorithm. This is the percentage by which the deltas are increased by if
	 * the partial derivative is greater than zero.
	 */
	const POSITIVE_ETA = 1.2;
	/**
	 * The NEGATIVE ETA value. This is specified by the resilient propagation
	 * algorithm. This is the percentage by which the deltas are increased by if
	 * the partial derivative is less than zero.
	 */
	const NEGATIVE_ETA = 0.5;
	/**
	 * The minimum delta value for a weight matrix value.
	 */
	const DELTA_MIN = 1e-6;
	/**
	 * The starting update for a delta.
	 */
	const DEFAULT_INITIAL_UPDATE = 0.1;
	/**
	 * The maximum amount a delta can reach.
	 */
	const DEFAULT_MAX_STEP = 50;

	private function __construct() {
	}
}
