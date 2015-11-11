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
namespace encog\test\engine\network\activation;

use encog\engine\network\activation\ActivationTANH;
use PHPUnit_Framework_TestCase as TestCase;
use SplFixedArray;

class ActivationTANHTest extends TestCase {
	public function testCreateActivationFunction() {
		$tanh = new ActivationTANH();
		$this->assertEquals($tanh, clone $tanh);
		$this->assertEquals($tanh, $tanh->clone());
		$this->assertTrue($tanh !== clone $tanh);
		$this->assertTrue($tanh !== $tanh->clone());
	}

	public function testActivationFunction() {
		$data = SplFixedArray::fromArray([-1.0, 0.0, 1.0, 42, M_PI, M_2_SQRTPI, M_E]);
		(new ActivationTANH())->activationFunction($data, 0, count($data));
		$this->assertEquals(tanh(-1), $data[0]);
		$this->assertEquals(tanh(0), $data[1]);
		$this->assertEquals(tanh(1), $data[2]);
		$this->assertEquals(tanh(42), $data[3]);
		$this->assertEquals(tanh(M_PI), $data[4]);
		$this->assertEquals(tanh(M_2_SQRTPI), $data[5]);
		$this->assertEquals(tanh(M_E), $data[6]);
	}

	public function testDerivativeFunction() {
		$data = SplFixedArray::fromArray([-1.0, 0.0, 1.0, 42, M_PI, M_2_SQRTPI, M_E]);
		$tanh = new ActivationTANH();
		foreach ($data as $value) {
			$this->assertEquals(1-$value*$value, $tanh->derivativeFunction($value,$value));
		}
	}

	public function testHasDerivative() {
		$this->assertTrue((new ActivationTANH())->hasDerivative());
	}

	public function testGetFactoryCode() {
		$this->assertEquals("TANH", (new ActivationTANH())->getFactoryCode());
	}

	public function testGetLabel() {
		$this->assertEquals("tanh", (new ActivationTANH())->getLabel());
	}

	public function testParams() {
		$tanh = new ActivationTANH();
		$tanh->setParam(0, 1);

		$this->assertEquals([], $tanh->getParamNames());
		$this->assertEquals([], $tanh->getParams());
	}
}
