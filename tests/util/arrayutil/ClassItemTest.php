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
namespace encog\test\util\arrayutil;

use encog\util\arrayutil\ClassItem;
use PHPUnit\Framework\TestCase;

class ClassItemTest extends TestCase {
	public function testClassItem() {
		$item = new ClassItem("abc", 0);

		$this->assertEquals("abc", $item->getName());
		$this->assertEquals(0, $item->getIndex());
		$this->assertToString($item);

		$item->setName("def");
		$item->setIndex(1);

		$this->assertToString($item);
	}

	private function assertToString(ClassItem $item) {
		$this->assertEquals(sprintf("[ClassItem name=%s, index=%d]", $item->getName(), $item->getIndex()), $item);
	}
}
