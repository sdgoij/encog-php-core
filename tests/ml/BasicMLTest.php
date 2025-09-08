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
namespace encog\test\ml;

use encog\ml\BasicML;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BasicMLTest extends TestCase {
	public function testProperties() {
		/** @var BasicML|MockObject $method */
		$method = $this->getMockForAbstractClass(BasicML::class);
		$this->assertEquals([], $method->getProperties());
		$this->assertNull($method->getProperty("abc"));

		$method->setProperty("a", 1);
		$method->setProperty("b", 0.5);
		$method->setProperty("c", "1.1");
		$method->setProperty("d", [1,2,3]);
		$method->setProperty("e", new class{
			public function __toString() {
				return "1";
			}
		});

		$this->assertCount(5, $method->getProperties());
		$this->assertSame(1, $method->getProperty("a"));
		$this->assertSame(1, $method->getPropertyLong("a"));
		$this->assertSame(1.0, $method->getPropertyDouble("a"));
		$this->assertSame("1", $method->getPropertyString("a"));

		$this->assertSame(0.5, $method->getProperty("b"));
		$this->assertSame(0, $method->getPropertyLong("b"));
		$this->assertSame(0.5, $method->getPropertyDouble("b"));
		$this->assertSame("0.5", $method->getPropertyString("b"));

		$this->assertSame("1.1", $method->getProperty("c"));
		$this->assertSame(1, $method->getPropertyLong("c"));
		$this->assertSame(1.1, $method->getPropertyDouble("c"));
		$this->assertSame("1.1", $method->getPropertyString("c"));

		$this->assertSame([1,2,3], $method->getProperty("d"));
		$this->assertSame(1, $method->getPropertyLong("d"));
		$this->assertSame(1.0, $method->getPropertyDouble("d"));
		$this->assertSame("", $method->getPropertyString("d"));

		$this->assertTrue(is_object($method->getProperty("e")));
		$this->assertSame(0, $method->getPropertyLong("e"));
		$this->assertSame(0.0, $method->getPropertyDouble("e"));
		$this->assertSame("1", $method->getPropertyString("e"));
	}
}
