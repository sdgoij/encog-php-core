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
namespace encog\test\neural\som\training\basic\neighborhood;

use encog\neural\som\training\basic\neighborhood\Bubble;
use PHPUnit\Framework\TestCase;

class BubbleTest extends TestCase {
	/**
	 * @param float $radius
	 * @param int   $current
	 * @param int   $best
	 * @param float $expected
	 *
	 * @dataProvider calculateParamsProvider
	 */
	public function testCalculate(float $radius, int $current, int $best, float $expected) {
		$bubble = new Bubble($radius);
		$this->assertSame($radius, $bubble->getRadius());
		$this->assertSame($expected, $bubble->calculate($current, $best));
		$bubble->setRadius($radius/10);
		$this->assertSame($radius/10, $bubble->getRadius());
	}

	public function calculateParamsProvider(): array {
		return [
			[10.0, 1, 1, 1.0],
			[0.1, 0, 1, 0.0],
			[1, 0, 1, 1.0],
		];
	}
}
