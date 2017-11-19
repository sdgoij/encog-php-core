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
namespace engine\network\activation;

use encog\engine\network\activation\ActivationReLU;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;
use SplFixedArray;

class ActivationReLUTest extends TestCase {
	public function testCreateActivationFunction() {
		$func = new ActivationReLU();
		$this->assertEquals($func, clone $func);
		$this->assertEquals($func, $func->clone());
		$this->assertTrue($func !== clone $func);
		$this->assertTrue($func !== $func->clone());
	}

	public function testActivationFunction() {
		$expect = [
			0.0, 0.45669937490248, 0.10382462308172,
			0.69260026210787, 0.0, 0.0,
			0.75336076315017, 0.0, 0.54053116429606,
			0.51553726237336, 0.90680313721151, 0.0,
			0.0, 0.69144058806461, 0.68915176948013,
			0.83305740689008, 0.097980510766376, 0.11803055560591,
			0.0, 0.5094490183497, 0.0,
			0.0, -0.066669475510992, 0.674121868226,
			0.5347480811825, 0.0, 0.0,
			0.95207595838161, 0.28242874427184, 0.0,
		];
		$values = SplFixedArray::fromArray([
			-0.46020889673201, 0.45669937490248, 0.10382462308172,
			0.69260026210787, -0.7796669121206, -0.81229019442372,
			0.75336076315017, -0.70639420281651, 0.54053116429606,
			0.51553726237336, 0.90680313721151, -0.63034439661016,
			-0.86780167747276, 0.69144058806461, 0.68915176948013,
			0.83305740689008, 0.097980510766376, 0.11803055560591,
			-0.55141067749724, 0.5094490183497, -0.68159867560195,
			-0.46043778604104, -0.066669475510992, 0.674121868226,
			0.5347480811825, -0.10362626327475, -0.16395951520104,
			0.95207595838161, 0.28242874427184, -0.67259598916968,
		]);

		(new ActivationReLU(-0.1, 0.0))->activationFunction($values, 0, count($values));
		$this->assertEquals($expect, $values->toArray());
	}

	public function testDerivativeFunction() {
		$expect = [1.0, 0.0, 0.0];
		$values = [1.0, 0.0, -1.0];
		$af = new ActivationReLU();
		foreach ($values as $key => $value) {
			$this->assertEquals($expect[$key], $af->derivativeFunction($value,$value));
		}
	}

	public function testHasDerivative() {
		$this->assertTrue((new ActivationReLU())->hasDerivative());
	}

	public function testGetFactoryCode() {
		$this->assertEquals("RELU[0,0]", (new ActivationReLU())->getFactoryCode());
	}

	public function testGetLabel() {
		$this->assertEquals("relu", (new ActivationReLU())->getLabel());
	}

	public function testParams() {
		$activation = new ActivationReLU(-M_PI, M_LN10);

		$this->assertEquals(["thresholdLow", "low"], $activation->getParamNames());
		$this->assertEquals([-M_PI, M_LN10], $activation->getParams());
		$this->assertEquals(-M_PI, $activation->getThresholdLow());
		$this->assertEquals(M_LN10, $activation->getLow());

		$activation->setParam(ActivationReLU::PARAM_RELU_LOW_THRESHOLD, -0.1);
		$activation->setParam(ActivationReLU::PARAM_RELU_LOW, 0.0);
		$this->assertEquals([-0.1, 0.0], $activation->getParams());

		$this->expectException(OutOfBoundsException::class);
		$activation->setParam(500, 1);
	}
}
