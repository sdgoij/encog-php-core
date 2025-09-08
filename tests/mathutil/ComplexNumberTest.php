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
namespace encog\test\mathutil;

use encog\mathutil\ComplexNumber;
use PHPUnit\Framework\TestCase;

class ComplexNumberTest extends TestCase {
	public function testMod() {
		$this->assertEquals(0.0, (new ComplexNumber(0,0))->mod());
		$this->assertEquals(1.0, (new ComplexNumber(0,1))->mod());
		$this->assertEquals(1.0, (new ComplexNumber(1,0))->mod());
		$this->assertEquals(1.4142135623730951, (new ComplexNumber(1,1))->mod());
		$this->assertEquals(2.23606797749979, (new ComplexNumber(1,2))->mod());
	}

	public function testArg() {
		$this->assertEquals(0.0, (new ComplexNumber(0,0))->arg());
		$this->assertEquals(1.5707963267948966, (new ComplexNumber(0,1))->arg());
		$this->assertEquals(0.0, (new ComplexNumber(1,0))->arg());
		$this->assertEquals(0.7853981633974483, (new ComplexNumber(1,1))->arg());
		$this->assertEquals(1.1071487177940904, (new ComplexNumber(1,2))->arg());
	}

	public function testConj() {
		$this->assertEquals(new ComplexNumber(0,0), (new ComplexNumber(0,0))->conj());
		$this->assertEquals(new ComplexNumber(1,-1), (new ComplexNumber(1,1))->conj());
		$this->assertEquals(new ComplexNumber(1,1), (new ComplexNumber(1,-1))->conj());
	}

	public function testPlus() {
		$n1 = new ComplexNumber(1,2);
		$n2 = new ComplexNumber(3,4);

		$this->assertEquals(new ComplexNumber(4,6), $n1->plus($n2));
		$this->assertNotEquals($n1, $n1->plus($n2));
	}

	public function testMinus() {
		$n1 = new ComplexNumber(4,3);
		$n2 = new ComplexNumber(2,1);

		$this->assertEquals(new ComplexNumber(2,2), $n1->minus($n2));
		$this->assertNotEquals($n1, $n1->minus($n2));
	}

	public function testTimes() {
		$n1 = new ComplexNumber(1,2);
		$n2 = new ComplexNumber(3,4);

		$this->assertEquals(new ComplexNumber(-5,10), $n1->times($n2));
		$this->assertNotEquals($n1, $n1->times($n2));
	}

	public function testDiv() {
		$n1 = new ComplexNumber(1,2);
		$n2 = new ComplexNumber(3,4);

		$this->assertEquals(new ComplexNumber(0.44,0.08), $n1->div($n2));
		$this->assertNotEquals($n1, $n1->div($n2));
	}

	public function testExp() {
		$n = new ComplexNumber(1,2);
		$this->assertEquals(new ComplexNumber(-1.1312043837568135, 2.4717266720048188), $n->exp());
		$this->assertNotEquals($n, $n->exp());
	}

	public function testLog() {
		$n = new ComplexNumber(1,2);
		$this->assertEquals(new ComplexNumber(0.8047189562170503, 1.1071487177940904), $n->log());
		$this->assertNotEquals($n, $n->log());
	}

	public function testSqrt() {
		$n = new ComplexNumber(1,2);
		$this->assertEquals(new ComplexNumber(1.272019649514069, 0.7861513777574233), $n->sqrt());
		$this->assertNotEquals($n, $n->sqrt());
	}

	public function testSin() {
		$n = new ComplexNumber(1,2);
		$this->assertEquals(new ComplexNumber(3.165778513216168, 1.9596010414216063), $n->sin());
		$this->assertNotEquals($n, $n->sin());
	}

	public function testCos() {
		$n = new ComplexNumber(1,2);
		$this->assertEquals(new ComplexNumber(2.0327230070196656, -3.0518977991518), $n->cos());
		$this->assertNotEquals($n, $n->cos());
	}

	public function testSinh() {
		$n = new ComplexNumber(1,2);
		$this->assertEquals(new ComplexNumber(-0.4890562590412937, 1.4031192506220405), $n->sinh());
		$this->assertNotEquals($n, $n->sinh());
	}

	public function testCosh() {
		$n = new ComplexNumber(1,2);
		$this->assertEquals(new ComplexNumber(-0.64214812471552, 1.0686074213827783), $n->cosh());
		$this->assertNotEquals($n, $n->cosh());
	}

	public function testTan() {
		$n = new ComplexNumber(1,2);
		$this->assertEquals(new ComplexNumber(0.0338128260798966, 1.0147936161466335), $n->tan());
		$this->assertNotEquals($n, $n->tan());
	}

	public function testChs() {
		$n = new ComplexNumber(1,2);
		$this->assertEquals(new ComplexNumber(-1, -2), $n->chs());
		$this->assertNotEquals($n, $n->chs());
	}

	public function testCopy() {
		$n = new ComplexNumber(1,2);
		$c = ComplexNumber::copy($n);

		$this->assertNotSame($n, $c);
		$this->assertEquals($n, $c);
	}

	public function testToString() {
		$this->assertEquals("1 + 1i", new ComplexNumber(1, 1));
		$this->assertEquals("1 - 1i", new ComplexNumber(1, -1));
		$this->assertEquals("1", new ComplexNumber(1, 0));
		$this->assertEquals("0", new ComplexNumber(0, 0));
		$this->assertEquals("1i", new ComplexNumber(0, 1));
		$this->assertEquals("NAN + i*NAN", new ComplexNumber(NAN, NAN));
		$this->assertEquals("INF + i*NAN", new ComplexNumber(INF, NAN));
		$this->assertEquals("NAN", new ComplexNumber(NAN, 0));
		$this->assertEquals("INF + INFi", new ComplexNumber(INF, INF));
		$this->assertEquals("1 + INFi", new ComplexNumber(1, INF));
		$this->assertEquals("1 + i*NAN", new ComplexNumber(1, NAN));
	}
}
