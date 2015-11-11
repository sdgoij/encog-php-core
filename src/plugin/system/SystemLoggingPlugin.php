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
namespace encog\plugin\system;

use DateTime;
use Throwable;

use encog\plugin\EncogPluginBase;
use encog\plugin\EncogPluginLogging1;
use encog\util\logging\EncogLogging;

/**
 * This is the built-in logging plugin for Encog. This plugin provides simple
 * file and console logging.
 */
class SystemLoggingPlugin implements EncogPluginLogging1 {
	public static function getStackTrace(Throwable $e): string {
		return $e->getTraceAsString();
	}
	public function getPluginType(): int {
		// this is a "type 1" plugin
		return 1;
	}

	public function getPluginServiceType(): int {
		return EncogPluginBase::TYPE_LOGGING;
	}

	public function getPluginName(): string {
		return "HRI-System-Logging";
	}

	public function getPluginDescription(): string {
		return "This is the built in logging for Encog, it logs to either a file or stdout";
	}

	public function getLogLevel(): int {
		return $this->currentLevel;
	}

	public function logException(int $level, Throwable $e) {
		$this->log($level, self::getStackTrace($e));
	}

	public function log(int $level, string $message) {
		if ($this->currentLevel <= $level) {
			switch ($level) {
				case EncogLogging::LEVEL_CRITICAL:
					$levelStr = "CRITICAL";
					break;
				case EncogLogging::LEVEL_ERROR:
					$levelStr = "ERROR";
					break;
				case EncogLogging::LEVEL_INFO:
					$levelStr = "INFO";
					break;
				case EncogLogging::LEVEL_DEBUG:
					$levelStr = "DEBUG";
					break;
				default:
					$levelStr = "?";
			}
			file_put_contents("php://stdout", sprintf(
				"%s [%s][1]: %s",
				(new DateTime())->format(DateTime::RFC3339),
				$levelStr,
				$message
			));
		}
	}

	/** @var int */
	private $currentLevel = EncogLogging::LEVEL_DISABLE;

	/** @var bool */
	private $logConsole = false;
}
