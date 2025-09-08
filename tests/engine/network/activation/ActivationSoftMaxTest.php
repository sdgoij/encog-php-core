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

use encog\engine\network\activation\ActivationSoftMax;
use PHPUnit\Framework\TestCase;
use SplFixedArray;

class ActivationSoftMaxTest extends TestCase {
	public function testCreateActivationFunction() {
		$activation = new ActivationSoftMax();
		$this->assertEquals($activation, clone $activation);
		$this->assertEquals($activation, $activation->clone());
		$this->assertTrue($activation !== clone $activation);
		$this->assertTrue($activation !== $activation->clone());
	}

	public function testActivationFunction() {
		$data = SplFixedArray::fromArray([1.0, 1.0, 1.0, 1.0]);
		(new ActivationSoftMax())->activationFunction($data, 0, count($data));
		$this->assertEquals(0.25, $data[0]);
		$this->assertEquals(0.25, $data[1]);
		$this->assertEquals(0.25, $data[2]);
		$this->assertEquals(0.25, $data[3]);
		$this->assertEquals(
			1.0, (new ActivationSoftMax())->derivativeFunction($data[0], $data[0])
		);
	}

	public function testActivationNaN() {
		$data = SplFixedArray::fromArray([NAN, NAN]);
		(new ActivationSoftMax())->activationFunction($data, 0, count($data));
		$this->assertEquals(0.5, $data[0]);
		$this->assertEquals(0.5, $data[1]);
		$this->assertEquals(
			1.0, (new ActivationSoftMax())->derivativeFunction($data[0], $data[0])
		);
	}

	public function testHasDerivative() {
		$this->assertTrue((new ActivationSoftMax())->hasDerivative());
	}

	public function testGetFactoryCode() {
		$this->assertEquals("SOFTMAX", (new ActivationSoftMax())->getFactoryCode());
	}

	public function testGetLabel() {
		$this->assertEquals("softmax", (new ActivationSoftMax())->getLabel());
	}

	public function testParams() {
		$activation = new ActivationSoftMax();
		$activation->setParam(0, 1);

		$this->assertEquals([], $activation->getParamNames());
		$this->assertEquals([], $activation->getParams());
	}
}
