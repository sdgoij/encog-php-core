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
namespace encog\test\mathutil\matrices;

use encog\test\util\PrivateConstructorTest;
use encog\mathutil\matrices\Matrix;
use encog\mathutil\matrices\MatrixError;
use encog\mathutil\matrices\MatrixMath;
use PHPUnit\Framework\TestCase;
use Throwable;

class MatrixMathTest extends TestCase {
	use PrivateConstructorTest;

	protected function getSubjectClassName(): string {
		return MatrixMath::class;
	}

	public function testAdd() {
		$this->assertEquals(new Matrix([[]]), MatrixMath::add(
			new Matrix([[]]),
			new Matrix([[]])
		));
		$this->assertEquals(new Matrix([[1,2]]), MatrixMath::add(
			new Matrix([[1,0]]),
			new Matrix([[0,2]])
		));
		$this->assertEquals(new Matrix([[1,2], [3,3]]), MatrixMath::add(
			new Matrix([[1,0], [1,2]]),
			new Matrix([[0,2], [2,1]])
		));
	}

	public function testAddRowMismatch() {
		$this->expectException(MatrixError::class);
		$this->expectExceptionMessage(
			"To add the matrices they must have the same number of rows and columns. ".
			"Matrix a has 2 rows and matrix b has 3 rows.");

		MatrixMath::add(new Matrix([[1], [2]]), new Matrix([[1], [2], [3]]));
	}

	public function testAddColumnMismatch() {
		$this->expectException(MatrixError::class);
		$this->expectExceptionMessage(
			"To add the matrices they must have the same number of rows and columns. ".
			"Matrix a has 2 cols and matrix b has 3 cols.");

		MatrixMath::add(new Matrix([[1,2]]), new Matrix([[1,2,3]]));
	}

	public function testCopy() {
		$source = new Matrix([[1.0,2.0], [3.0,4.0]]);
		$target = Matrix::createZero(2, 2);
		MatrixMath::copy($source, $target);

		$this->assertTrue($source->equals($target));
		$this->assertEquals($source, $target);
	}

	public function testDeleteCol() {
		$source = new Matrix([[1.0,2.0], [3.0,4.0]]);
		$expect = new Matrix([[2.0], [4.0]]);

		$this->assertEquals($expect, MatrixMath::deleteCol($source, 0));

		$this->expectException(MatrixError::class);
		MatrixMath::deleteCol(new Matrix([[]]), 3);
	}

	public function testDeleteRow() {
		$source = new Matrix([[1.0,2.0], [3.0,4.0]]);
		$expect = new Matrix([[3.0,4.0]]);

		$this->assertEquals($expect, MatrixMath::deleteRow($source, 0));

		$this->expectException(MatrixError::class);
		MatrixMath::deleteRow(new Matrix([[]]), 3);
	}

	public function testDeterminant() {
		$this->assertEquals(-2.0, MatrixMath::determinant(
			new Matrix([[1.0,2.0], [3.0,4.0]])
		));
	}

	public function testDivide() {
		$m = MatrixMath::divide(new Matrix([[2.0,4.0], [6.0,8.0]]), 2.0);
		$this->assertEquals(1.0, $m->get(0, 0));
		$this->assertEquals(2.0, $m->get(0, 1));
		$this->assertEquals(3.0, $m->get(1, 0));
		$this->assertEquals(4.0, $m->get(1, 1));
	}

	public function testDotProduct() {
		$this->assertEquals(70.0, MatrixMath::dotProduct(
			new Matrix([[1, 2, 3, 4]]),
			new Matrix([[5, 6, 7, 8]])
		));
		$this->assertEquals(70.0, MatrixMath::dotProduct(
			new Matrix([[1, 2, 3, 4]]),
			new Matrix([[5], [6], [7], [8]])
		));
		$this->assertEquals(70.0, MatrixMath::dotProduct(
			new Matrix([[1], [2], [3], [4]]),
			new Matrix([[5, 6, 7, 8]])
		));
		$this->assertEquals(70.0, MatrixMath::dotProduct(
			new Matrix([[1], [2], [3], [4]]),
			new Matrix([[5], [6], [7], [8]])
		));

		try {
			MatrixMath::dotProduct(new Matrix([[1,2]]), new Matrix([[1, 2], [2, 3]]));
		} catch (MatrixError $e) {
			$this->assertEquals("To take the dot product, both matrices must be vectors.", $e->getMessage());
		} catch (Throwable $e) {
			$this->fail("Unexpected Exception: " . $e->getMessage());
		}

		$this->expectException(MatrixError::class);
		$this->expectExceptionMessage("To take the dot product, both matrices must be of the same length.");
		MatrixMath::dotProduct(new Matrix([[1,2]]), new Matrix([[1]]));
	}

	public function testIdentity() {
		$this->assertTrue(MatrixMath::identity(2)->equals(new Matrix([[1,0], [0,1]])));
		$this->expectException(MatrixError::class);
		$this->expectExceptionMessage("Identity matrix must be at least of size 1.");
		MatrixMath::identity(0);
	}

	public function testMultiply() {
		$m1 = new Matrix([[1,4], [2,5], [3,6]]);
		$m2 = new Matrix([[7,8,9], [10, 11, 12]]);
		$m3 = new Matrix([[47,52,57], [64,71,78], [81,90,99]]);
		$m4 = MatrixMath::multiply($m1, $m2);

		$this->assertTrue($m3->equals($m4));
		$this->assertEquals($m3, $m4);

		$this->expectException(MatrixError::class);
		MatrixMath::multiply($m2, Matrix::createZero(1,5));
	}

	public function testMultiplyBy() {
		$m = MatrixMath::multiplyBy(new Matrix([[1,2], [3,4]]), 2);
		$this->assertEquals(2, $m->get(0, 0));
		$this->assertEquals(4, $m->get(0, 1));
		$this->assertEquals(6, $m->get(1, 0));
		$this->assertEquals(8, $m->get(1, 1));
	}

	public function testMultiplyVector() {
		$v = MatrixMath::multiplyVector(new Matrix([[1,2], [3,4]]), [2,3]);
		$this->assertEquals(8, $v[0]);
		$this->assertEquals(18, $v[1]);
	}

	public function testSubtract() {
		$this->assertEquals(new Matrix([[]]), MatrixMath::subtract(
			new Matrix([[]]),
			new Matrix([[]])
		));
		$this->assertEquals(new Matrix([[1,0]]), MatrixMath::subtract(
			new Matrix([[1,2]]),
			new Matrix([[0,2]])
		));
		$this->assertEquals(new Matrix([[1,0], [1,2]]), MatrixMath::subtract(
			new Matrix([[1,2], [3,3]]),
			new Matrix([[0,2], [2,1]])
		));
	}

	public function testSubtractRowMismatch() {
		$this->expectException(MatrixError::class);
		$this->expectExceptionMessage(
			"To subtract the matrices they must have the same number of rows and columns. ".
			"Matrix a has 2 rows and matrix b has 0 rows.");

		MatrixMath::subtract(new Matrix([[1,2], [1,2]]), new Matrix([]));
	}

	public function testSubtractColumnMismatch() {
		$this->expectException(MatrixError::class);
		$this->expectExceptionMessage(
			"To subtract the matrices they must have the same number of rows and columns. ".
			"Matrix a has 2 cols and matrix b has 3 cols.");

		MatrixMath::subtract(new Matrix([[1,2]]), new Matrix([[1,2,3]]));
	}

	public function testTranspose() {
		$this->assertEquals(new Matrix([[1],[2],[3],[4]]), MatrixMath::transpose(
			new Matrix([[1,2,3,4]])
		));
		$this->assertEquals(new Matrix([[1,2,3,4]]), MatrixMath::transpose(
			new Matrix([[1],[2],[3],[4]])
		));
	}

	public function testTestVectorLength() {
		$this->assertEquals(5.477225575051661, MatrixMath::vectorLength(
			new Matrix([[1.0,2.0,3.0,4.0]])
		));
		$this->expectException(MatrixError::class);
		MatrixMath::vectorLength(Matrix::createZero(2,2));
	}
}
