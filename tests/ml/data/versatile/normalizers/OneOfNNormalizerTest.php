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
namespace encog\test\ml\data\versatile\normalizers;

use encog\EncogError;
use encog\ml\data\basic\BasicMLData;
use encog\ml\data\versatile\columns\ColumnDefinition;
use encog\ml\data\versatile\columns\ColumnType;
use encog\ml\data\versatile\normalizers\OneOfNNormalizer;
use PHPUnit\Framework\TestCase;

class OneOfNNormalizerTest extends TestCase {
	public function testOutputSize() {
		$column = new ColumnDefinition("a", new ColumnType(ColumnType::ignore));
		$column->setClasses(["A", "B", "C"]);
		$this->assertEquals(count($column->getClasses()),
			(new OneOfNNormalizer(0, 1))->outputSize($column));
	}

	public function testNormalizeColumn() {
		$column = new ColumnDefinition("A", new ColumnType(ColumnType::nominal));
		$column->setClasses(["A", "B", "C"]);
		$normalizer = new OneOfNNormalizer(0, 1);
		$output = [];

		$this->assertEquals(3, $normalizer->normalizeColumn($column, "A", $output, 0));
		$this->assertEquals(6, $normalizer->normalizeColumn($column, "B", $output, 3));
		$this->assertEquals(9, $normalizer->normalizeColumn($column, "C", $output, 6));
		$this->assertEquals(12, $normalizer->normalizeColumn($column, "D", $output, 9));
		$this->assertEquals([1,0,0, 0,1,0, 0,0,1, 0,0,0], $output);
	}

	public function testNormalizeColumnDouble() {
		$this->expectException(EncogError::class);
		$this->expectExceptionMessage("Can't use a one-of-n normalizer on a continuous value: 3.14");
		$column = new ColumnDefinition("A", new ColumnType(ColumnType::continuous));
		$output = [];

		(new OneOfNNormalizer(0, 1))->normalizeColumnDouble($column, 3.14, $output, 0);
	}

	public function testDenormalizeColumn() {
		$column = new ColumnDefinition("A", new ColumnType(ColumnType::nominal));
		$column->setClasses(["A", "B", "C"]);
		$normalizer = new OneOfNNormalizer(0, 1);

		$this->assertEquals("A", $normalizer->denormalizeColumn($column, new BasicMLData([1,0,0]), 0));
		$this->assertEquals("B", $normalizer->denormalizeColumn($column, new BasicMLData([0,1,0]), 0));
		$this->assertEquals("C", $normalizer->denormalizeColumn($column, new BasicMLData([0,0,1]), 0));
	}
}
