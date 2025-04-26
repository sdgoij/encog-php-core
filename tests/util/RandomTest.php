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
namespace encog\test\util;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

use encog\util\Random;

class RandomTest extends TestCase {
	const MAX_INT_BOUND  = 1 << 28;
	const MAX_LONG_BOUND = 1 << 42;
	const NCALLS         = 10000;

	public function testRandomSequence() {
		$random = new Random(1);
		$values = [
				3139097972, 627294208, 2319013888, 2142961664, 1692377088,
				1563934720, 3346882560, 350388224, 1672549376, 4204666880,
		];
		foreach ($values as $value) {
			$this->assertEquals($value, $random->nextInt());
		}
	}

	public function testNextInt() {
		$r = new Random();
		$f = $r->nextInt();
		$i = 0;

		while ($i < self::NCALLS && $r->nextInt() == $f) ++$i;
		$this->assertTrue($i < self::NCALLS);

		$r1 = new Random(42);
		$r2 = new Random(42);
		$i = 0;

		while ($i++ < 1000) {
			$this->assertSame($r1->nextInt(), $r2->nextInt());
		}
	}

	public function testNextLong() {
		$r = new Random();
		$f = $r->nextLong();
		$i = 0;

		while ($i < self::NCALLS && $r->nextLong() == $f) ++$i;
		$this->assertTrue($i < self::NCALLS);
	}

	public function testNextBoolean() {
		$r = new Random();
		$f = $r->nextBoolean();
		$i = 0;

		while ($i < self::NCALLS && $r->nextBoolean() == $f) ++$i;
		$this->assertTrue($i < self::NCALLS);
	}

	public function testNextFloat() {
		$r = new Random();
		$f = $r->nextFloat();
		$i = 0;

		while ($i < self::NCALLS && $r->nextFloat() == $f) ++$i;
		$this->assertTrue($i < self::NCALLS);
	}

	public function testNextDouble() {
		$r = new Random();
		$f = $r->nextDouble();
		$i = 0;

		while ($i < self::NCALLS && $r->nextDouble() == $f) ++$i;
		$this->assertTrue($i < self::NCALLS);
	}

	public function testNextGaussian() {
		$r = new Random();
		$f = $r->nextGaussian();
		$i = 0;

		while ($i < self::NCALLS && $r->nextGaussian() == $f) ++$i;
		$this->assertTrue($i < self::NCALLS);
	}

	public function testNextIntBoundedNeg() {
		$this->expectException(InvalidArgumentException::class);
		$r = new Random();
		$r->nextInt(-17);
	}

	public function testNextIntBounded() {
		$r = new Random();
		for ($bound = 2; $bound < self::MAX_INT_BOUND; $bound += 524959) {
			$f = $r->nextInt($bound);
			$this->assertTrue($f >= 0 && $f < $bound);
			$i = $j = 0;
			while ($i < self::NCALLS && ($j = $r->nextInt($bound)) == $f) {
				$this->assertTrue($j >= 0 && $j < $bound);
				$i++;
			}
			$this->assertTrue($i < self::NCALLS);
		}
	}

	public function testNextBytes() {
		$result1 = array_fill(0, 1024, 0.0);
		$result2 = array_fill(0, 1024, 0.0);

		(new Random(1))->nextBytes($result1);
		(new Random(1))->nextBytes($result2);

		$this->assertSame($result1, $result2);
	}
}
