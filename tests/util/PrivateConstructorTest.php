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

use ReflectionClass;

trait PrivateConstructorTest {
	abstract protected function getSubjectClassName(): string;

	abstract public function assertEquals($expected, $actual, string $message = ''): void;
	abstract public function assertTrue($condition, string $message = ''): void;

	public function testPrivateConstructor() {
		$r = new ReflectionClass($this->getSubjectClassName());
		$c = $r->getConstructor();
		$c->setAccessible(true);
		$c->invoke($r->newInstanceWithoutConstructor());

		$this->assertEquals(0, $c->getNumberOfParameters());
		$this->assertTrue($c->isPrivate());
	}
}
