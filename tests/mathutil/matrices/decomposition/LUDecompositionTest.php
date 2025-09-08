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
namespace encog\test\mathutil\matrices\decomposition;

use InvalidArgumentException;
use encog\mathutil\matrices\MatrixError;
use encog\mathutil\matrices\decomposition\LUDecomposition;
use encog\mathutil\matrices\Matrix;
use PHPUnit\Framework\TestCase;
use Throwable;

class LUDecompositionTest extends TestCase {
	public function testIsNonSingular() {
		$this->assertTrue((new LUDecomposition(new Matrix([[1.0,2.0], [3.0,4.0]])))->isNonSingular());
		$this->assertFalse((new LUDecomposition(Matrix::createZero(2, 2)))->isNonSingular());
	}

	public function testGetL() {
		$LU = new LUDecomposition(new Matrix([[1,2], [3,4]]));
		$this->assertEquals(new Matrix([[1,0], [1/3,1]]), $LU->getL());
	}

	public function testGetU() {
		$LU = new LUDecomposition(new Matrix([[1,2], [3,4]]));
		$this->assertEquals(new Matrix([[3,4], [0.0,0.6666666666666667]]), $LU->getU()); // TODO how did that broke?
	}

	public function testGetPivot() {
		$LU = new LUDecomposition(new Matrix([[1,2], [3,4]]));
		$this->assertEquals([1, 0], $LU->getPivot());
	}

	public function testGetDoublePivot() {
		$LU = new LUDecomposition(new Matrix([[1,2], [3,4]]));
		$this->assertEquals([1.0, 0.0], $LU->getDoublePivot());
	}

	public function testDet() {
		$LU = new LUDecomposition(new Matrix([[1,2], [3,4]]));
		$this->assertEquals(-2, $LU->det());

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage("Matrix must be square.");
		(new LUDecomposition(Matrix::createZero(2,3)))->det();
	}

	public function testSolve() {
		$LU = new LUDecomposition(new Matrix([[1,2], [3,4]]));

		$this->assertEquals(Matrix::createZero(2, 3), $LU->solve(Matrix::createZero(2, 3)));
		$this->assertEquals(new Matrix([[1,0], [0,1]]), $LU->solve(new Matrix([[1,2], [3,4]])));
		$this->assertEquals(new Matrix([[0], [1]]), $LU->solve(new Matrix([[2], [4]])));
		$this->assertEquals(new Matrix([[0,0,0], [.5,.5,.5]]), $LU->solve(new Matrix([[1,1,1], [2,2,2]])));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage("Matrix row dimensions must agree.");
		$LU->solve(Matrix::createZero(3,2));
	}

	public function testSolveSingular() {
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage("Matrix is singular.");
		$m = Matrix::createZero(2, 2);
		(new LUDecomposition($m))->solve($m);
	}

	public function testSolveArray() {
		$LU = new LUDecomposition(new Matrix([[1,2], [3,4]]));
		$this->assertEquals([0,0.5], $LU->solveArray([1,2]));

		try {
			$LU->solveArray([]);
			$this->fail("Expected MatrixError");
		} catch (MatrixError $e) {
			$this->assertEquals("Empty values.", $e->getMessage());
		} catch (Throwable $e) {
			$this->fail("Unexpected Error: {$e->getMessage()}");
		}

		try {
			$LU->solveArray([1]);
			$this->fail("Expected MatrixError");
		} catch (MatrixError $e) {
			$this->assertEquals("Invalid matrix dimensions.", $e->getMessage());
		} catch (Throwable $e) {
			$this->fail("Unexpected Error: {$e->getMessage()}");
		}

		$this->expectException(MatrixError::class);
		$this->expectExceptionMessage("Matrix is singular");
		(new LUDecomposition(Matrix::createZero(5, 5)))->solveArray([1,2,3,4,5]);
	}

	public function testInverse() {
		$LU = new LUDecomposition(new Matrix([[1,2], [3,4]]));
		$this->assertEquals([[-1.9999999999999998,1.0], [1.4999999999999998,-0.49999999999999994]], $LU->inverse());
		$this->expectException(MatrixError::class);
		$this->expectExceptionMessage("Matrix is singular");
		(new LUDecomposition(Matrix::createZero(5, 5)))->inverse();
	}
}
