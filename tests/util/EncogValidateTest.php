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
namespace encog\test\util;

use encog\ml\data\basic\BasicMLDataSet;
use encog\neural\NeuralNetworkError;
use encog\test\neural\networks\XORUtil;
use encog\util\EncogValidate;
use PHPUnit\Framework\TestCase;

class EncogValidateTest extends TestCase {
	use PrivateConstructorTest;

	public function testValidateNetworkForTrainingOK() {
		EncogValidate::validateNetworkForTraining(
			XORUtil::createThreeLayerNetwork(),
			XORUtil::createDataSet()
		);
		$this->assertTrue(true);
	}

	public function testValidateNetworkForTrainingInputErr() {
		$this->expectException(NeuralNetworkError::class);
		EncogValidate::validateNetworkForTraining(
			XORUtil::createThreeLayerNetwork(),
			new BasicMLDataSet(XORUtil::IDEAL)
		);
	}

	public function testValidateNetworkForTrainingIdealErr() {
		$this->expectException(NeuralNetworkError::class);
		EncogValidate::validateNetworkForTraining(
			XORUtil::createThreeLayerNetwork(),
			new BasicMLDataSet(XORUtil::INPUT, XORUtil::INPUT)
		);
	}

	protected function getSubjectClassName(): string {
		return EncogValidate::class;
	}
}
