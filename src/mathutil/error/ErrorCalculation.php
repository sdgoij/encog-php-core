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
namespace encog\mathutil\error;

require_once __DIR__ . "/ErrorCalculationMode.php";

/**
 * Calculate the error of a neural network. Encog currently supports three error
 * calculation modes. See ErrorCalculationMode for more info.
 */
class ErrorCalculation {
	private static $mode;

	public static function getMode(): ErrorCalculationMode {
		if (!self::$mode) {
			self::$mode = new ErrorCalculationMode(ErrorCalculationMode::MSE);
		}
		return self::$mode;
	}

	public static function setMode(ErrorCalculationMode $mode) {
		self::$mode = $mode;
	}

	public static function restore() {
		self::$mode = null;
	}

	/** @var float */
	private $globalError;

	/** @var int */
	private $setSize;

	public final function calculate(): float {
		$mode = self::getMode();
		if ($mode == new ErrorCalculationMode(ErrorCalculationMode::MSE)) {
			$error = $this->calculateMSE();
		} else if ($mode == new ErrorCalculationMode(ErrorCalculationMode::ESS)) {
			$error = $this->calculateESS();
		} else {
			$error = $this->calculateRMS();
		}
		return $error;
	}

	public final function calculateRMS(): float {
		if ($this->setSize > 0) {
			return sqrt($this->globalError / $this->setSize);
		}
		return 0;
	}

	public final function calculateMSE(): float {
		if ($this->setSize > 0) {
			return $this->globalError / $this->setSize;
		}
		return 0;
	}

	public final function calculateESS(): float {
		if ($this->setSize > 0) {
			return $this->globalError / 2;
		}
		return 0;
	}

	public function reset() {
		$this->globalError = 0;
		$this->setSize = 0;
	}

	public final function updateErrorArray($actual, $ideal, float $significance) {
		for ($i = 0; $i < count($actual); $i++) {
			$delta = ($ideal[$i] - $actual[$i]) * $significance;
			$this->globalError += $delta * $delta;
		}
		$this->setSize += count($ideal);
	}

	public final function updateError(float $actual, float $ideal) {
		$delta = $ideal - $actual;
		$this->globalError += $delta * $delta;
		$this->setSize++;
	}
}
