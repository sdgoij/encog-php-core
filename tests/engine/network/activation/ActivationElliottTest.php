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

use encog\engine\network\activation\ActivationElliott;
use PHPUnit_Framework_TestCase as TestCase;
use SplFixedArray;

class ActivationElliottTest extends TestCase {
	public function testCreateActivationFunction() {
		$activation = new ActivationElliott();
		$this->assertEquals($activation, $activation->clone());
		$this->assertTrue($activation !== clone $activation);
	}

	public function testActivationFunction() {
		$expect = [
			0.5134478043694801, 0.3160744190787664, 0.8433669278452838,
			0.1721188408466504, 0.15860590857749884, 0.09784193148842896,
			0.8887228848258472, 0.2540697056219543, 0.10427595685594776,
			0.5890465498976633, 0.6524640337632616, 0.7700105026377607,
			0.07452471800510091, 0.8874763205511611, 0.12110886953155064,
		];
		$values = SplFixedArray::fromArray([
			0.027638975818520817, -0.5819059367642119, 2.1921738692970205,
			-1.904969598566353, -2.152467675916925, -4.110283417280364,
			3.4932868651158095, -0.9679638655699483, -3.7949691863362727,
			0.21668281377243254, 0.4387000154666133, 1.1740123167993513,
			-5.709183387527572, 3.4435091569089256, -3.128516779439863,
		]);

		(new ActivationElliott())->activationFunction($values, 0, count($values));
		$this->assertEquals($expect, $values->toArray());
	}

	public function testDerivativeFunction() {
		$expect = [0.125, 0.5, 0.125];
		$values = [1.0, 0.0, -1.0];
		$af = new ActivationElliott();
		foreach ($values as $key => $value) {
			$this->assertEquals($expect[$key], $af->derivativeFunction($value,$value));
		}
	}

	public function testHasDerivative() {
		$this->assertTrue((new ActivationElliott())->hasDerivative());
	}

	public function testGetFactoryCode() {
		$this->assertEquals("ELLIOTT[1]", (new ActivationElliott())->getFactoryCode());
	}

	public function testGetLabel() {
		$this->assertEquals("elliott", (new ActivationElliott())->getLabel());
	}

	public function testParams() {
		$activation = new ActivationElliott();

		$this->assertEquals(["Slope"], $activation->getParamNames());
		$this->assertEquals([1.0], $activation->getParams());

		$activation->setParam(0, 0.5);
		$this->assertEquals([0.5], $activation->getParams());
	}
}
