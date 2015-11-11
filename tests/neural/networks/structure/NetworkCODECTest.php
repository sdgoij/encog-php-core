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
namespace encog\test\neural\networks\structure;

use encog\ml\MLEncodable;
use encog\ml\MLMethod;
use encog\neural\networks\structure\NetworkCODEC;
use encog\neural\NeuralNetworkError;
use PHPUnit_Framework_TestCase as TestCase;

class NetworkCODECTest extends TestCase {
	public function testNetworkSize() {
		$this->assertEquals(5, NetworkCODEC::networkSize(
			new class([1,2,3,4,5]) implements MLMethod, MLEncodable {
				public function __construct(array $weights = []) { $this->weights = $weights; }
				public function encodedArrayLength(): int { return count($this->weights); }
				public function encodeToArray(array &$data) { $data = $this->weights; }
				public function decodeFromArray(array $data) { $this->weights = $data; }
				private $weights;
			}));
		$this->setExpectedExceptionRegExp(NeuralNetworkError::class,
			"/^This machine learning method cannot be encoded/");
		NetworkCODEC::networkSize(new class implements MLMethod {});
	}
}
