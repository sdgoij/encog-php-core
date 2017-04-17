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
namespace encog\util\logging;

use encog\Encog;
use Throwable;

/**
 * This class provides logging for Encog. Programs using Encog can make use of
 * it as well. All logging is passed on to the current logging plugin. By
 * default the SystemLoggingPlugin is used.
 */
class EncogLogging {
	const LEVEL_DEBUG    = 0;
	const LEVEL_INFO     = 1;
	const LEVEL_ERROR    = 2;
	const LEVEL_CRITICAL = 3;
	const LEVEL_DISABLE  = 4;

	public static final function log(int $level, string $message) {
		Encog::getInstance()->getLoggingPlugin()->log($level, $message);
	}

	public static final function logException(Throwable $e, int $level = self::LEVEL_ERROR) {
		Encog::getInstance()->getLoggingPlugin()->logException($level, $e);
	}

	public final function getCurrentLevel(): int {
		Encog::getInstance()->getLoggingPlugin()->getLogLevel();
	}
}
