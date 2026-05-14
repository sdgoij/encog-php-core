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
namespace encog\test\engine\network\activation;

use encog\engine\network\activation\ActivationSteepenedSigmoid;
use PHPUnit\Framework\TestCase;
use SplFixedArray;

class ActivationSteepenedSigmoidTest extends TestCase {
	public function testCreateActivationFunction() {
		$activation = new ActivationSteepenedSigmoid();
		$this->assertEquals($activation, clone $activation);
		$this->assertEquals($activation, $activation->clone());
		$this->assertTrue($activation !== clone $activation);
		$this->assertTrue($activation !== $activation->clone());
	}

	public function testActivationFunction() {
		$data = SplFixedArray::fromArray([0.0, 1.0]);
		(new ActivationSteepenedSigmoid())->activationFunction($data, 0, count($data));
		$this->assertEquals(0.5, $data[0]);
		$this->assertEquals(0.9926084586557181, $data[1]);
		$this->assertEquals(
			0.15151469914589297, (new ActivationSteepenedSigmoid())->derivativeFunction($data[0], $data[0])
		);
		$this->assertEquals(
			0.0014095628830591302, (new ActivationSteepenedSigmoid())->derivativeFunction($data[1], $data[1])
		);
	}

	public function testDerivativeFunction() {
		$activation = new ActivationSteepenedSigmoid();
		$this->assertEquals(6.002500000000001, $activation->derivativeFunction(0.0, 0.0));
		$this->assertEquals(0.0013117835514959563, $activation->derivativeFunction(1.0, 1.0));
	}

	public function testHasDerivative() {
		$this->assertTrue((new ActivationSteepenedSigmoid())->hasDerivative());
	}

	public function testGetFactoryCode() {
		$this->assertEquals("SSIGMOID", (new ActivationSteepenedSigmoid())->getFactoryCode());
	}

	public function testGetLabel() {
		$this->assertEquals("steepenedsigmoid", (new ActivationSteepenedSigmoid())->getLabel());
	}

	public function testParams() {
		$activation = new ActivationSteepenedSigmoid();
		// @phpstan-ignore method.resultUnused
		$activation->setParam(0, 1);

		$this->assertEquals([], $activation->getParamNames());
		$this->assertEquals([], $activation->getParams());
	}
}
