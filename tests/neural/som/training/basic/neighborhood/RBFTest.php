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

use encog\mathutil\rbf\RBFType;
use encog\neural\NeuralNetworkError;
use encog\neural\som\training\basic\neighborhood\RBF;
use PHPUnit\Framework\TestCase;

class RBFTest extends TestCase {
	/**
	 * @param RBFType $type
	 * @param array   $size
	 * @param int     $current
	 * @param int     $best
	 * @param float   $expected
	 *
	 * @dataProvider calculateParamsProvider
	 */
	public function testCalculate(RBFType $type, array $size, int $current, int $best, float $expected) {
		$func = new RBF($type, $size);
		$this->assertEquals(count($size), $func->getRBF()->getDimensions());
		$result = $func->calculate($current, $best);
		$this->assertSame($expected, $result);
	}

	public function calculateParamsProvider(): array {
		return [
			[new RBFType(RBFType::Gaussian), [1,2,3], 0, 1, 0.6065306597126334],
			[new RBFType(RBFType::Gaussian), [1,2,3], 1, 0, 0.6065306597126334],
			[new RBFType(RBFType::Gaussian), [1,2,3], 1, 2, 0.36787944117144233],
			[new RBFType(RBFType::Gaussian), [1,2,3], 2, 1, 0.36787944117144233],
		];
	}

	public function testCreate2D() {
		$func = RBF::create2D(new RBFType(RBFType::MexicanHat), 1, 1);
		$this->assertSame(2, $func->getRBF()->getDimensions());
		$this->assertSame(1.0, $func->getRadius());
	}

	public function testCreate3D() {
		$func = new RBF(new RBFType(RBFType::Multiquadric), [1, 1, 1]);
		$this->assertSame(3, $func->getRBF()->getDimensions());
	}

	public function testUnknownType() {
		$this->expectException(NeuralNetworkError::class);
		$this->expectExceptionMessage("Unknown RBF type.");
		new RBF(new RBFType(RBFType::InverseMultiquadric), [1]);
		new RBF(new RBFType(1000), [1]);
	}
}
