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
namespace encog\test\mathutil\error;

use encog\mathutil\error\ErrorCalculation;
use encog\mathutil\error\ErrorCalculationMode;
use PHPUnit\Framework\TestCase;

class ErrorCalculationTest extends TestCase {
	public function testCalculationMode() {
		$RMS = new ErrorCalculationMode(ErrorCalculationMode::RMS);
		$MSE = new ErrorCalculationMode(ErrorCalculationMode::MSE);
		$ESS = new ErrorCalculationMode(ErrorCalculationMode::ESS);
		$this->assertEquals($MSE, ErrorCalculation::getMode());

		ErrorCalculation::setMode($ESS);
		$this->assertEquals($ESS, ErrorCalculation::getMode());

		ErrorCalculation::setMode($RMS);
		$this->assertEquals($RMS, ErrorCalculation::getMode());

		ErrorCalculation::restore();
		$this->assertEquals($MSE, ErrorCalculation::getMode());
	}

	public function testCalculate() {
		$calculate = function(ErrorCalculation $error, int $mode): float {
			ErrorCalculation::setMode(new ErrorCalculationMode($mode));
			return $error->calculate();
		};
		$err = new ErrorCalculation();
		$err->updateError(1, 2);

		$this->assertEquals(1.0, $calculate($err, ErrorCalculationMode::MSE));
		$this->assertEquals(1.0, $calculate($err, ErrorCalculationMode::RMS));
		$this->assertEquals(0.5, $calculate($err, ErrorCalculationMode::ESS));

		ErrorCalculation::restore();
	}

	public function testCalculateRMS() {
		$this->assertEquals(0, (new ErrorCalculation())->calculateRMS());

		$err = new ErrorCalculation();
		$err->updateError(1, 2);
		$this->assertEquals(1.0, $err->calculateRMS());

		$err->updateErrorArray([1,2,3], [0.9,1.8,2.7], 1.0);
		$this->assertEquals(0.5338539126015656, $err->calculateRMS());
	}

	public function testCalculateMSE() {
		$this->assertEquals(0, (new ErrorCalculation())->calculateMSE());

		$err = new ErrorCalculation();
		$err->updateError(1, 2);
		$this->assertEquals(1.0, $err->calculateMSE());

		$err->updateErrorArray([1,2,3], [0.9,1.8,2.7], 1.0);
		$this->assertEquals(0.285, $err->calculateMSE());
	}

	public function testCalculateESS() {
		$this->assertEquals(0, (new ErrorCalculation())->calculateESS());

		$err = new ErrorCalculation();
		$err->updateError(1, 2);
		$this->assertEquals(0.5, $err->calculateESS());

		$err->updateErrorArray([1,2,3], [0.9,1.8,2.7], 1.0);
		$this->assertEquals(0.57, $err->calculateESS());
	}

	public function testReset() {
		$err = new ErrorCalculation();
		$err->updateErrorArray([1,2,3], [0.9,1.8,2.7], 1.0);
		$err->reset();

		$this->assertEquals(0.0, $err->calculate());
	}
}
