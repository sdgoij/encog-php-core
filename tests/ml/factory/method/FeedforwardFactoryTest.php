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
namespace encog\test\ml\factory\method;

use encog\EncogError;
use encog\ml\factory\method\FeedforwardFactory;
use encog\neural\networks\BasicNetwork;
use PHPUnit\Framework\TestCase;

class FeedforwardFactoryTest extends TestCase {
	public function testCreate() {
		$method = (new FeedforwardFactory())->create("?:B->SIGMOID->4:B->SIGMOID->?", 2, 1);
		assert($method instanceof BasicNetwork);

		$this->assertEquals(2, $method->getInputCount());
		$this->assertEquals(1, $method->getOutputCount());
		$this->assertEquals(3, $method->getLayerCount());
	}

	public function testCreateInputError() {
		$this->expectException(EncogError::class);
		$this->expectExceptionMessage("Must have at least one input for feedforward.");
		(new FeedforwardFactory())->create("", 0, 0);
	}

	public function testCreateOutputError() {
		$this->expectException(EncogError::class);
		$this->expectExceptionMessage("Must have at least one output for feedforward.");
		(new FeedforwardFactory())->create("", 1, 0);
	}

	public function testCreateQuestionError() {
		$this->expectException(EncogError::class);
		$this->expectExceptionMessage("Only two ?'s may be used.");
		(new FeedforwardFactory())->create("?->?->?", 1, 1);
	}


	public function testCreateLayerError() {
		$this->expectException(EncogError::class);
		$this->expectExceptionMessage("Layer can't have zero neurons, Unknown architecture element: ?->TANH->X()->?, can't parse: X");
		(new FeedforwardFactory())->create("?->TANH->X()->?", 1, 1);
	}
}
