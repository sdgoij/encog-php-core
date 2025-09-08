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
namespace encog\test\mathutil\randomize;

use encog\EncogError;
use encog\mathutil\matrices\Matrix;
use encog\mathutil\randomize\NguyenWidrowRandomizer;
use encog\ml\MLMethod;
use PHPUnit\Framework\TestCase;

class NguyenWidrowRandomizerTest extends TestCase {
	public function testRandomizeMLMethod() {
		$this->expectException(EncogError::class);
		$this->expectExceptionMessage("Nguyen-Widrow only supports BasicNetwork.");
		(new NguyenWidrowRandomizer())->randomize(
			new class implements MLMethod {}
		);
	}

	public function testRandomizeArray() {
		$this->expectException(EncogError::class);
		$this->expectExceptionMessage(NguyenWidrowRandomizer::MESSAGE);
		(new NguyenWidrowRandomizer())->randomizeArray($this->v);
	}

	public function testRandomizeArray2D() {
		$this->expectException(EncogError::class);
		$this->expectExceptionMessage(NguyenWidrowRandomizer::MESSAGE);
		(new NguyenWidrowRandomizer())->randomizeArray2D($this->v);
	}

	public function testRandomizeFloat() {
		$this->expectException(EncogError::class);
		$this->expectExceptionMessage(NguyenWidrowRandomizer::MESSAGE);
		(new NguyenWidrowRandomizer())->randomizeFloat(1.0);
	}

	public function testRandomizeMatrix() {
		$this->expectException(EncogError::class);
		$this->expectExceptionMessage(NguyenWidrowRandomizer::MESSAGE);
		(new NguyenWidrowRandomizer())->randomizeMatrix(Matrix::createZero(1,1));
	}

	private $v = [];
}
