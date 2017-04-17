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
namespace encog;

use RuntimeException;
use Throwable;

use encog\util\logging\EncogLogging;

/**
 * General error class for Encog. All Encog errors should extend from this
 * class. Doing this ensures that they will be caught as Encog errors. This also
 * ensures that any subclasses will be logged.
 */
class EncogError extends RuntimeException {
	public function __construct($message = "", $code = 0, Throwable $previous = null) {
		parent::__construct($message, $code, $previous);
		EncogLogging::log(EncogLogging::LEVEL_ERROR, $message);
	}
}
