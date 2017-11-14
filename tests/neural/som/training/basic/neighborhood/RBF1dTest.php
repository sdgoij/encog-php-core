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

use encog\mathutil\rbf\RadialBasisFunction;
use encog\mathutil\rbf\RBFType;
use encog\neural\som\training\basic\neighborhood\RBF1d;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class RBF1dTest extends TestCase {
	public function testCalculate() {
		/** @var RadialBasisFunction|MockObject $rbf */
		$rbf = $this->createMock(RadialBasisFunction::class);
		$rbf->expects($this->once())->method("calculate");
		$rbf->expects($this->once())->method("getWidth")->willReturn(1.0);
		$rbf->expects($this->once())->method("setWidth");
		$func = new RBF1d($rbf);
		$func->setRadius(3.2);
		$func->calculate(1,1);

		$this->assertSame(1.0, $func->getRadius());
	}

	public function testFromType() {
		$this->assertSame(1.0, RBF1d::fromType(new RBFType(RBFType::Gaussian))->getRadius());
	}
}
