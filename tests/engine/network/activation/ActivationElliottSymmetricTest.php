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

use encog\engine\network\activation\ActivationElliottSymmetric;
use PHPUnit\Framework\TestCase;
use SplFixedArray;

class ActivationElliottSymmetricTest extends TestCase {
	public function testCreateActivationFunction() {
		$activation = new ActivationElliottSymmetric();
		$this->assertEquals($activation, $activation->clone());
		$this->assertTrue($activation !== clone $activation);
	}

	public function testActivationFunction() {
		$expect = [
			0.0268956087389603, -0.36785116184246724, 0.6867338556905674,
			-0.6557623183066992, -0.6827881828450023, -0.8043161370231421,
			0.7774457696516943, -0.4918605887560914, -0.7914480862881045,
			0.17809309979532653, 0.3049280675265231, 0.5400210052755214,
			-0.8509505639897982, 0.7749526411023223, -0.7577822609368987,
		];
		$values = SplFixedArray::fromArray([
			0.027638975818520817, -0.5819059367642119, 2.1921738692970205,
			-1.904969598566353, -2.152467675916925, -4.110283417280364,
			3.4932868651158095, -0.9679638655699483, -3.7949691863362727,
			0.21668281377243254, 0.4387000154666133, 1.1740123167993513,
			-5.709183387527572, 3.4435091569089256, -3.128516779439863,
		]);

		(new ActivationElliottSymmetric())->activationFunction($values, 0, count($values));
		$this->assertEquals($expect, $values->toArray());
	}

	public function testDerivativeFunction() {
		$expect = [0.25, 1.0, 0.25];
		$values = [1.0, 0.0, -1.0];
		$af = new ActivationElliottSymmetric();
		foreach ($values as $key => $value) {
			$this->assertEquals($expect[$key], $af->derivativeFunction($value,$value));
		}
	}

	public function testHasDerivative() {
		$this->assertTrue((new ActivationElliottSymmetric())->hasDerivative());
	}

	public function testGetFactoryCode() {
		$this->assertEquals("ELLIOTTSYMMETRIC[1]", (new ActivationElliottSymmetric())->getFactoryCode());
	}

	public function testGetLabel() {
		$this->assertEquals("elliottsymmetric", (new ActivationElliottSymmetric())->getLabel());
	}

	public function testParams() {
		$activation = new ActivationElliottSymmetric();

		$this->assertEquals(["Slope"], $activation->getParamNames());
		$this->assertEquals([1.0], $activation->getParams());

		$activation->setParam(0, 0.5);
		$this->assertEquals([0.5], $activation->getParams());
	}
}
