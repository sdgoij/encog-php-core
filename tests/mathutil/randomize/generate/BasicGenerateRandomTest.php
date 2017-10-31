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

use encog\mathutil\randomize\generate\BasicGenerateRandom;
use PHPUnit\Framework\TestCase;

class BasicGenerateRandomTest extends TestCase {
	public function testBasicGenerateRandom() {
		$g1 = new BasicGenerateRandom(1);
		$g2 = new BasicGenerateRandom(2);

		$this->assertNotEquals($g1->nextGaussian(), $g2->nextGaussian());
		$this->assertNotEquals($g1->nextGaussian(), $g2->nextGaussian());
		$this->assertNotEquals($g1->nextGaussian(), $g1->nextGaussian());
		$this->assertNotEquals($g2->nextGaussian(), $g2->nextGaussian());

		$this->assertSame($g1->nextBoolean(), $g2->nextBoolean());

		$this->assertNotEquals($g1->nextInt(), $g2->nextInt());
		$this->assertNotEquals($g1->nextInt(PHP_INT_MAX), $g2->nextInt(PHP_INT_MAX));
		$this->assertNotEquals($g1->nextInt(13), $g2->nextInt(37));

		$this->assertNotEquals($g1->nextFloat(), $g2->nextFloat());
		$this->assertNotEquals($g1->nextLong(), $g2->nextLong());

	}
}
