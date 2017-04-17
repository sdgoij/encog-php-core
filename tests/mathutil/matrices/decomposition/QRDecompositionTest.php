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
namespace encog\test\mathutil\matrices\decomposition;

use encog\mathutil\matrices\decomposition\QRDecomposition;
use encog\mathutil\matrices\Matrix;
use encog\mathutil\matrices\MatrixError;
use PHPUnit_Framework_TestCase as TestCase;
use Throwable;

class QRDecompositionTest extends TestCase {
	public function testIsFullRank() {
		$this->assertTrue((new QRDecomposition(new Matrix([[1,2], [3,4]])))->isFullRange());
		$this->assertFalse((new QRDecomposition(Matrix::createZero(1, 2)))->isFullRange());
		$this->assertFalse((new QRDecomposition(Matrix::createZero(2, 1)))->isFullRange());
	}

	public function testGetH() {
		$expect = new Matrix([[1.316227766016838, 0.0], [0.9486832980505138, 2.0]]);
		$this->assertEquals($expect, (new QRDecomposition(new Matrix([[1,2], [3,4]])))->getH());
	}

	public function testGetR() {
		$expect = new Matrix([[-3.1622776601683795, -4.427188724235731], [0.0, 0.6324555320336751]]);
		$this->assertEquals($expect, (new QRDecomposition(new Matrix([[1,2], [3,4]])))->getR());
	}

	public function testGetQ() {
		$expect = new Matrix([[-0.316227766016838, 0.9486832980505138], [-0.9486832980505138, -0.316227766016838]]);
		$this->assertEquals($expect, (new QRDecomposition(new Matrix([[1,2], [3,4]])))->getQ());
	}

	public function testSolve() {
		$m1 = new Matrix([[1,0,0,0], [0,1,0,0], [0,0,1,0], [0,0,0,1]]);
		$m2 = new Matrix([[17,18,19,20], [21,22,23,24], [25,27,28,29], [37,33,31,30]]);
		$m3 = (new QRDecomposition($m1))->solve($m2);
		$this->assertEquals(17, $m3->get(0, 0));
		$this->assertEquals(22, $m3->get(1, 1));
		$this->assertEquals(28, $m3->get(2, 2));
		$this->assertEquals(30, $m3->get(3, 3));
		$this->assertEquals(4, $m3->getRows());
		$this->assertEquals(4, $m3->getCols());

		try {
			(new QRDecomposition($m1))->solve(new Matrix([[1]]));
			$this->fail("Expected MatrixError");
		} catch (MatrixError $e) {
			$this->assertEquals("Matrix row dimensions must agree.", $e->getMessage());
		} catch (Throwable $e) {
			$this->fail("Unexpected Error: {$e->getMessage()}");
		}

		$this->expectException(MatrixError::class);
		$this->expectExceptionMessage("Matrix is rank deficient.");
		(new QRDecomposition(Matrix::createZero(1,2)))->solve(Matrix::createZero(1,2));
	}
}
