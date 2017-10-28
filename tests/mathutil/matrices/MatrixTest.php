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

use encog\mathutil\matrices\Matrix;
use encog\mathutil\matrices\MatrixError;
use encog\mathutil\matrices\MatrixMath;
use PHPUnit_Framework_TestCase as TestCase;
use RangeException;

class MatrixTest extends TestCase {
	public function testEquals() {
		$m1 = new Matrix([[1,2], [3,4]]);
		$m2 = new Matrix([[1,2], [3,4]]);
		$m3 = new Matrix([[1], [2]]);

		$this->assertEquals($m1, $m2);
		$this->assertNotEquals($m1, $m3);
		$this->assertTrue($m1->equals($m2));
		$this->assertFalse($m2->equals($m3));
		$this->assertFalse($m1->equals(42));
	}

	public function testEqualsPrecision() {
		$m1 = new Matrix([[1.1234, 2.123], [3.123, 4.123]]);
		$m2 = new Matrix([[1.123, 2.123], [3.123, 4.123]]);

		$this->assertFalse($m1->equals($m2, 4));
		$this->assertTrue($m1->equals($m2, 3));
	}

	public function testEqualsNegativePrecision() {
		$this->expectException(MatrixError::class);
		$this->expectExceptionMessage("Precision can't be a negative number.");

		(new Matrix([[1,2],[3,4]]))->equals(new Matrix([]), -1);
	}

	public function testEqualsBigPrecision() {
		$this->expectException(MatrixError::class);
		$this->expectExceptionMessage("Precision of 302 decimal places is not supported.");

		(new Matrix([[1,2],[3,4]]))->equals(new Matrix([[4,3],[2,1]]), 302);
	}

	public function testRowCol() {
		$matrix = Matrix::createZero(6, 3);
		$matrix->setRowCol(1, 2, 1.5);

		$this->assertEquals(6, $matrix->getRows());
		$this->assertEquals(3, $matrix->getCols());
		$this->assertEquals(1.5, $matrix->get(1, 2));
	}

	public function testZero() {
		$this->assertFalse((new Matrix([[1], [1]]))->isZero());
		$this->assertTrue((new Matrix([[0,0], [0,0]]))->isZero());
		$this->assertTrue(Matrix::createZero(10, 10)->isZero());
	}

	public function testSum() {
		$this->assertEquals(1+2+3+4, (new Matrix([[1,2],[3,4]]))->sum());
	}

	public function testRowMatrix() {
		$m = Matrix::createRowMatrix([1.0,2.0,3.0,4.0]);
		$this->assertEquals(1, $m->getRows());
		$this->assertEquals(4, $m->getCols());
		$this->assertEquals(1.0, $m->get(0, 0));
		$this->assertEquals(2.0, $m->get(0, 1));
		$this->assertEquals(3.0, $m->get(0, 2));
		$this->assertEquals(4.0, $m->get(0, 3));
	}

	public function testColumnMatrix() {
		$m = Matrix::createColumnMatrix([1.0,2.0,3.0,4.0]);
		$this->assertEquals(4, $m->getRows());
		$this->assertEquals(1, $m->getCols());
		$this->assertEquals(1.0, $m->get(0, 0));
		$this->assertEquals(2.0, $m->get(1, 0));
		$this->assertEquals(3.0, $m->get(2, 0));
		$this->assertEquals(4.0, $m->get(3, 0));
	}

	public function testBoolean() {
		$m1 = new Matrix([[true,false], [false,true]]);
		$m2 = new Matrix([[1, -1], [-1, 1]]);
		$this->assertTrue($m1->equals($m2));
	}

	public function testAdd() {
		$m = Matrix::createColumnMatrix([1,2,3]);
		$m->add(0, 0, 1);

		$this->assertEquals(2, $m->get(0, 0));

		$this->expectMatrixError(function () use ($m) { $m->add(3,0,0); }, "The row: 3 is out of range: 3");
		$this->expectMatrixError(function () use ($m) { $m->add(2,1,0); }, "The column: 1 is out of range: 1");
	}

	public function testAddMatrix() {
		$m = new Matrix([[1,2], [3,4]]);
		$m->addMatrix(new Matrix([[4,3], [2,1]]));

		$this->assertEquals(5, $m->get(0, 0));
		$this->assertEquals(5, $m->get(0, 1));
		$this->assertEquals(5, $m->get(1, 0));
		$this->assertEquals(5, $m->get(1, 1));

		$m->addMatrix(new Matrix([[1], [1]]));

		$this->assertEquals(6, $m->get(0, 0));
		$this->assertEquals(5, $m->get(0, 1));
		$this->assertEquals(6, $m->get(1, 0));
		$this->assertEquals(5, $m->get(1, 1));
	}

	public function testClear() {
		$m = new Matrix([[1,2], [3,4]]);
		$m->clear();

		$this->assertEquals(0, $m->get(0, 0));
		$this->assertEquals(0, $m->get(0, 1));
		$this->assertEquals(0, $m->get(1, 0));
		$this->assertEquals(0, $m->get(1, 1));
	}

	public function testIsVector() {
		$this->assertFalse((new Matrix([[1,2], [3,4]]))->isVector());
		$this->assertTrue(Matrix::createColumnMatrix([1,2,3,4])->isVector());
		$this->assertTrue(Matrix::createRowMatrix([1,2,3,4])->isVector());
	}

	public function testMultiply() {
		$m1 = new Matrix([[1,2], [3,4]]);
		$m2 = new Matrix([[2,4], [6,8]]);
		$m1->multiply(2);

		$this->assertEquals($m2, $m1);
		$this->assertTrue($m1->equals($m2));
	}

	public function testMultiplyVector() {
		$v = (new Matrix([[1,2], [3,4]]))->multiplyVector([2,3]);
		$this->assertEquals(8, $v[0]);
		$this->assertEquals(18, $v[1]);
	}

	public function testToPackedArray() {
		$m = new Matrix([[1,2], [3,4]]);
		$p = $m->toPackedArray();

		$this->assertEquals($m->get(0, 0), $p[0]);
		$this->assertEquals($m->get(0, 1), $p[1]);
		$this->assertEquals($m->get(1, 0), $p[2]);
		$this->assertEquals($m->get(1, 1), $p[3]);
	}

	public function testFromPackedArray() {
		$m = Matrix::createZero(2, 2);
		$p = [1, 2, 3, 4];
		$m->fromPackedArray($p);

		$this->assertEquals($p[0], $m->get(0, 0));
		$this->assertEquals($p[1], $m->get(0, 1));
		$this->assertEquals($p[2], $m->get(1, 0));
		$this->assertEquals($p[3], $m->get(1, 1));
	}

	public function testSize() {
		$this->assertEquals(4, (new Matrix([[1,2], [3,4]]))->size());
	}

	public function testRandomize() {
		$m1 = Matrix::createZero(3, 3);
		$m2 = clone $m1;

		$this->assertEquals($m1, $m2);
		$m2->randomize(-1.0, 1.0);
		$this->assertNotEquals($m1, $m2);

		foreach ($m2->toPackedArray() as $value) {
			$this->assertGreaterThanOrEqual(-1, $value);
			$this->assertLessThanOrEqual(1, $value);
		}
	}

	public function testIsSquare() {
		$this->assertFalse(Matrix::createColumnMatrix([1,2,3,4])->isSquare());
		$this->assertFalse(Matrix::createRowMatrix([1,2,3,4])->isSquare());
		$this->assertFalse(Matrix::createZero(2, 3)->isSquare());
		$this->assertTrue(Matrix::createZero(2, 2)->isSquare());
	}

	public function testGetMatrix() {
		$m1 = new Matrix([[1,2,3], [4,5,6], [7,8,9]]);
		$m2 = $m1->getMatrix([0,1,2], 0, 1);
		$m3 = $m1->getMatrix([1,2], 1, 2);

		$this->assertEquals(new Matrix([[1,2], [4,5], [7,8]]), $m2);
		$this->assertEquals(new Matrix([[5,6], [8,9]]), $m3);

		$this->expectException(RangeException::class);
		$m1->getMatrix([0,1,2], 0, 10);
	}

	public function testToString() {
		$this->assertEquals("[Matrix: rows=3,cols=2]", (string)new Matrix([[1,2],[3,4],[5,6]]));
		$this->assertEquals("[Matrix: rows=2,cols=3]", (string)new Matrix([[1,2,3],[4,5,6]]));
	}

	public function testSet() {
		$matrix = new Matrix([[1,2],[3,4]]);
		$matrix->set(1.2);

		$this->assertEquals([[1.2,1.2],[1.2,1.2]], $matrix->getData());
	}

	public function testFromMatrix() {
		$matrix = new Matrix([[1,2,3],[4,5,6]]);
		$matrix->setFromMatrix(new Matrix([[4,3], [2,1]]));

		$this->assertEquals([[4,3,0],[2,1,0]], $matrix->getData());
	}

	public function testGetRow() {
		$matrix = Matrix::createZero(2,2);
		$test = function (int $row) use ($matrix) {
			return function () use ($matrix, $row): Matrix {
				return $matrix->getRow($row);
			};
		};
		$this->expectMatrixError($test(-100), "Row -100 does not exist.");
		$this->expectMatrixError($test(-1), "Row -1 does not exist.");
		$this->expectMatrixError($test(2), "Row 2 does not exist.");
		$this->expectMatrixError($test(3), "Row 3 does not exist.");
		$this->expectMatrixError($test(42), "Row 42 does not exist.");

		try {
			$this->assertEquals([[0,0]], ($test(0)())->getData());
			$this->assertEquals([[0,0]], ($test(1)())->getData());
		} catch (MatrixError $e) {
			$this->fail("unexpected exception: {$e->getMessage()}");
		}
	}

	private function expectMatrixError(callable $fn, string $message) {
		try {
			$fn();
			$this->fail("Failed asserting that exception of type \"".MatrixError::class."\" is thrown.");
		} catch (MatrixError $e) {
			$this->assertEquals($message, $e->getMessage(),
				"Failed asserting that exception message '{$e->getMessage()}' contains '$message."
			);
		}
	}

	public function testHashCode() {
		$this->assertEquals(0 % PHP_INT_MAX, Matrix::createZero(100, 100)->hashCode());
	}

	public function testInverse() {
		$this->assertEquals([[3,2],[4,3]], (new Matrix([[3,-2], [-4,3]]))->inverse()->getData());
		$this->assertInstanceOf(Matrix::class, (new Matrix([[1,2],[3,4],[5,6]]))->inverse());

		$this->expectException(MatrixError::class);
		$this->expectExceptionMessage("Matrix is rank deficient.");
		(new Matrix([[1,2,3],[4,5,6]]))->inverse();
	}
}
