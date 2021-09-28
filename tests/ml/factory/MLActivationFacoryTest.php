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
namespace ml\factory;

namespace encog\test\ml\factory;

use encog\Encog;
use encog\engine\network\activation\ActivationFunction;
use encog\ml\factory\MLActivationFactory;
use PHPUnit\Framework\TestCase;

class MLActivationFactoryTest extends TestCase {
	public function testCreateMethod() {
		$factory = new MLActivationFactory();
		$this->assertInstanceOf(ActivationFunction::class, $factory->create("TEST_OK"));
		$this->assertNull($factory->create("TEST_FAIL"));
	}
	public function setUp(): void {
		Encog::getInstance()->reset()->registerPlugin(new DummyPlugin());
	}
	public function tearDown(): void {
		Encog::getInstance()->reset();
		parent::tearDown();
	}
}
