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
namespace encog\mathutil\error;

use encog\util\spl\types\SplEnum;

/**
 * Selects the error calculation mode for Encog.
 */
class ErrorCalculationMode extends SplEnum {
	/**
	 * Root mean square error.
	 */
	const RMS = 1;
	/**
	 * Mean square error.
	 */
	const MSE = 2;
	/**
	 * Sum of Squares error.
	 */
	const ESS = 3;
}
