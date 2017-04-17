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
namespace encog\util;

class Runtime {
	public static function getAvailableProcessors(): int {
		if (null === self::$availableProcessors) {
			self::$availableProcessors = 1;
			if (is_file("/proc/cpuinfo")) {
				$cpuinfo = file_get_contents("/proc/cpuinfo");
				preg_match_all("/^processor/im", $cpuinfo, $m);
				self::$availableProcessors = count($m);
			} else if ('WIN' == strtoupper(substr(PHP_OS, 0, 3))) {
				if (false !== $p = @popen("wmic cpu get NumberOfCores", "rb")) {
					fgets($p);
					self::$availableProcessors = intval(fgets($p));
					pclose($p);
				}
			} else {
				if (false !== $p = @popen("sysctl -n hw.ncpu", "rb")) {
					self::$availableProcessors = intval(stream_get_contents($p));
					pclose($p);
				}
			}
		}
		return self::$availableProcessors;
	}
	private static $availableProcessors = null;
}
