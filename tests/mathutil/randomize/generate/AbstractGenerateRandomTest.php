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
namespace encog\test\mathutil\randomize\generate;

use encog\mathutil\randomize\generate\AbstractBoxMuller;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class AbstractGenerateRandomTest extends TestCase {
	public function testNextDouble() {
		/** @var AbstractBoxMuller|MockObject $generator */
		$generator = $this->getMockForAbstractClass(AbstractBoxMuller::class);
		$generator->expects($this->once())->method("next")->willReturn(0.5);

		$this->assertSame(0.5, $generator->nextDouble(1.0, 0.0));
	}

	public function testNextInt() {
		/** @var AbstractBoxMuller|MockObject $generator */
		$generator = $this->getMockForAbstractClass(AbstractBoxMuller::class);
		$generator->expects($this->once())->method("next")->willReturn(0.5);

		$this->assertSame(1, $generator->nextInt(1.0, 0.0));
	}
}
