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
namespace encog\test\mathutil\rbf;

use encog\mathutil\rbf\BasicRBF;
use encog\mathutil\rbf\RadialBasisFunction;
use PHPUnit\Framework\TestCase;

class BasicRBFTest extends TestCase {
	public function testCreateSingleDimensional() {
		$rbf = DummyRBF::createSingleDimensional(0.5, 1.0, 1.0);
		$this->assertEquals(1, $rbf->getDimensions());
		$this->assertEquals([0.5], $rbf->getCenters());
		$this->assertEquals(0.5, $rbf->getCenter(0));
		$this->assertEquals(1.0, $rbf->getPeak());
		$this->assertEquals(1.0, $rbf->getWidth());
	}

	public function testCreateMultiDimensional() {
		$rbf = DummyRBF::createMultiDimensional(5.0, [1.25, 2.5, 3.75], 15.0);
		$this->assertEquals(3, $rbf->getDimensions());
		$this->assertEquals([1.25, 2.5, 3.75], $rbf->getCenters());
		$this->assertEquals(1.25, $rbf->getCenter(0));
		$this->assertEquals(2.5, $rbf->getCenter(1));
		$this->assertEquals(3.75, $rbf->getCenter(2));
		$this->assertEquals(5.0, $rbf->getPeak());
		$this->assertEquals(15.0, $rbf->getWidth());
	}

	public function testCreateZeroCentered() {
		$rbf = DummyRBF::createZeroCentered(1);
		$this->assertEquals(1, $rbf->getDimensions());
		$this->assertEquals([0.0], $rbf->getCenters());
		$this->assertEquals(0.0, $rbf->getCenter(0));
		$this->assertEquals(1.0, $rbf->getPeak());
		$this->assertEquals(1.0, $rbf->getWidth());
	}

	public function testCenterAccess() {
		$rbf = $this->createDummyRBF();
		$this->assertEquals([], $rbf->getCenters());
		$rbf->setCenters([0.0]);
		$rbf->getCenters()[0] = 0.5;
		$this->assertEquals(0.5, $rbf->getCenter(0));
		$this->assertEquals(1, $rbf->getDimensions());
	}

	public function testGetDimensions() {
		$rbf = $this->createDummyRBF();
		$rbf->setCenters([0.25, 0.75]);
		$this->assertEquals(2, $rbf->getDimensions());
	}

	public function testPeakAccess() {
		$rbf = $this->createDummyRBF();
		$this->assertEquals(0.0, $rbf->getPeak());
		$rbf->setPeak(1.0);
		$this->assertEquals(1.0, $rbf->getPeak());
	}

	public function testWidthAccess() {
		$rbf = $this->createDummyRBF();
		$this->assertEquals(0.0, $rbf->getWidth());
		$rbf->setWidth(1.0);
		$this->assertEquals(1.0, $rbf->getWidth());
	}

	private function createDummyRBF(): RadialBasisFunction {
		return new DummyRBF();
	}
}

final class DummyRBF extends BasicRBF {
	function calculate(array $values): float {
		return 0.1;
	}
}
