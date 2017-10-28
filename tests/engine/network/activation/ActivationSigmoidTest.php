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
namespace encog\test\engine\network\activation;

use encog\engine\network\activation\ActivationSigmoid;
use PHPUnit\Framework\TestCase;
use SplFixedArray;

class ActivationSigmoidTest extends TestCase {
	public function testCreateActivationFunction() {
		$sigmoid = new ActivationSigmoid();
		$this->assertEquals($sigmoid, clone $sigmoid);
		$this->assertEquals($sigmoid, $sigmoid->clone());
		$this->assertTrue($sigmoid !== clone $sigmoid);
		$this->assertTrue($sigmoid !== $sigmoid->clone());
	}

	public function testActivationFunction() {
		$data = SplFixedArray::fromArray([-1.0, 0.0, 1.0, 42, M_PI, M_2_SQRTPI, M_E]);
		(new ActivationSigmoid())->activationFunction($data, 0, count($data));
		$this->assertEquals(self::a(-1), $data[0]);
		$this->assertEquals(self::a(0), $data[1]);
		$this->assertEquals(self::a(1), $data[2]);
		$this->assertEquals(self::a(42), $data[3]);
		$this->assertEquals(self::a(M_PI), $data[4]);
		$this->assertEquals(self::a(M_2_SQRTPI), $data[5]);
		$this->assertEquals(self::a(M_E), $data[6]);
	}

	public function testDerivativeFunction() {
		$data = SplFixedArray::fromArray([-1.0, 0.0, 1.0, 42, M_PI, M_2_SQRTPI, M_E]);
		$sigmoid = new ActivationSigmoid();
		foreach ($data as $value) {
			$this->assertEquals(self::d($value), $sigmoid->derivativeFunction($value,$value));
		}
	}

	public function testHasDerivative() {
		$this->assertTrue((new ActivationSigmoid())->hasDerivative());
	}

	public function testGetFactoryCode() {
		$this->assertEquals("SIGMOID", (new ActivationSigmoid())->getFactoryCode());
	}

	public function testGetLabel() {
		$this->assertEquals("sigmoid", (new ActivationSigmoid())->getLabel());
	}

	public function testParams() {
		$tanh = new ActivationSigmoid();
		$tanh->setParam(0, 1);

		$this->assertEquals([], $tanh->getParamNames());
		$this->assertEquals([], $tanh->getParams());
	}

	private static function a(float $v): float {
		return 1 / (1 + exp(-1 * $v));
	}

	private static function d(float $v): float {
		return $v * (1.0 - $v);
	}

	public function testInf() {
		$values = SplFixedArray::fromArray([INF, -INF]);
		(new ActivationSigmoid())->activationFunction($values, 0, 2);
		$this->assertEquals($values[0], 1);
		$this->assertEquals($values[1], 0);
	}
}
