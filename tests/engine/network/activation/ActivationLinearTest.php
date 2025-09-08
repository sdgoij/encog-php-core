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
namespace engine\network\activation;

use encog\engine\network\activation\ActivationLinear;
use PHPUnit\Framework\TestCase;
use SplFixedArray;

class ActivationLinearTest extends TestCase {
	public function testCreateActivationFunction() {
		$af = new ActivationLinear();
		$this->assertEquals($af, clone $af);
		$this->assertTrue($af !== clone $af);
	}

	public function testActivationFunction() {
		$data = SplFixedArray::fromArray([-1.0, 0.0, 1.0, 42, M_PI, M_2_SQRTPI, M_E]);
		(new ActivationLinear())->activationFunction($data, 0, count($data));
		$this->assertEquals(-1, $data[0]);
		$this->assertEquals(0, $data[1]);
		$this->assertEquals(1, $data[2]);
		$this->assertEquals(42, $data[3]);
		$this->assertEquals(M_PI, $data[4]);
		$this->assertEquals(M_2_SQRTPI, $data[5]);
		$this->assertEquals(M_E, $data[6]);
	}

	public function testDerivativeFunction() {
		$data = SplFixedArray::fromArray([-1.0, 0.0, 1.0, 42, M_PI, M_2_SQRTPI, M_E]);
		$af = new ActivationLinear();
		foreach ($data as $value) {
			$this->assertEquals(1, $af->derivativeFunction($value,$value));
		}
	}

	public function testHasDerivative() {
		$this->assertTrue((new ActivationLinear())->hasDerivative());
	}

	public function testGetFactoryCode() {
		$this->assertEquals("LINEAR", (new ActivationLinear())->getFactoryCode());
	}

	public function testGetLabel() {
		$this->assertEquals("linear", (new ActivationLinear())->getLabel());
	}

	public function testParams() {
		$tanh = new ActivationLinear();
		$tanh->setParam(0, 1);

		$this->assertEquals([], $tanh->getParamNames());
		$this->assertEquals([], $tanh->getParams());
	}
}
