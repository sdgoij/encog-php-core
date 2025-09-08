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
namespace encog\test\neural\networks\structure;

use encog\mathutil\randomize\RangeRandomizer;
use encog\ml\MLEncodable;
use encog\ml\MLMethod;
use encog\neural\networks\structure\NetworkCODEC;
use encog\neural\NeuralNetworkError;
use encog\test\util\PrivateConstructorTest;
use PHPUnit\Framework\TestCase;

class NetworkCODECTest extends TestCase {
	use PrivateConstructorTest;

	public function testNetworkSize() {
		$this->assertEquals(5, NetworkCODEC::networkSize($this->createEncodableMethod()));
		$this->expectException(NeuralNetworkError::class);
		$this->expectExceptionMessageMatches("/^This machine learning method cannot be encoded/");
		NetworkCODEC::networkSize(new class implements MLMethod {});
	}

	public function testNetworkToArray() {
		$this->assertEquals([1,2,3,4,5], NetworkCODEC::networkToArray($this->createEncodableMethod()));
		$this->expectException(NeuralNetworkError::class);
		$this->expectExceptionMessageMatches("/^This machine learning method cannot be encoded/");
		NetworkCODEC::networkToArray(new class implements MLMethod {});
	}

	public function testArrayToNetwork() {
		$values = array_fill(0, 255, 0.0);
		(new RangeRandomizer(-1.0, 1.0))->randomizeArray($values);
		$method = $this->createEncodableMethod();
		NetworkCODEC::arrayToNetwork($values, $method);

		$this->assertEquals($values, NetworkCODEC::networkToArray($method));
		$this->expectException(NeuralNetworkError::class);
		$this->expectExceptionMessageMatches("/^This machine learning method cannot be encoded/");
		NetworkCODEC::arrayToNetwork([], new class implements MLMethod {});
	}

	protected function getSubjectClassName(): string {
		return NetworkCODEC::class;
	}

	private function createEncodableMethod(): MLEncodable {
		return new class([1,2,3,4,5]) implements MLMethod, MLEncodable {
			public function __construct(array $weights = []) { $this->weights = $weights; }
			public function encodedArrayLength(): int { return count($this->weights); }
			public function encodeToArray(array &$data) { $data = $this->weights; }
			public function decodeFromArray(array $data) { $this->weights = $data; }
			private $weights;
		};
	}
}
