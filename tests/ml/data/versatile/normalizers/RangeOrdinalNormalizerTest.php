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
namespace encog\test\ml\data\versatile\normalizers;

use encog\EncogError;
use encog\ml\data\basic\BasicMLData;
use encog\ml\data\versatile\columns\ColumnDefinition;
use encog\ml\data\versatile\columns\ColumnType;
use encog\ml\data\versatile\normalizers\RangeOrdinalNormalizer;
use PHPUnit\Framework\TestCase;

class RangeOrdinalNormalizerTest extends TestCase {
	public function testOutputSize() {
		$column = new ColumnDefinition("a", new ColumnType(ColumnType::ignore));
		$this->assertEquals(1, (new RangeOrdinalNormalizer(0, 1))->outputSize($column));
	}

	public function testNormalizeColumn() {
		$column = new ColumnDefinition("A", new ColumnType(ColumnType::nominal));
		$column->setClasses(["A", "B", "C"]);
		$normalizer = new RangeOrdinalNormalizer(0, 1);
		$output = [];

		$this->assertEquals(1, $normalizer->normalizeColumn($column, "A", $output, 0));
		$this->assertEquals(2, $normalizer->normalizeColumn($column, "B", $output, 1));
		$this->assertEquals(3, $normalizer->normalizeColumn($column, "C", $output, 2));
		$this->assertEquals([0.0, 0.3333333333333333, 0.6666666666666666], $output);

		$nan = new RangeOrdinalNormalizer(NAN, NAN);
		$this->assertEquals(4, $nan->normalizeColumn($column, "A", $output, 3));
		$this->assertNan($output[3]);

		$this->expectExceptionMessage("Unknown ordinal: D");
		$this->expectException(EncogError::class);
		$this->assertEquals(4, $normalizer->normalizeColumn($column, "D", $output, 3));
	}

	public function testNormalizeColumnDouble() {
		$this->expectExceptionMessage("Can't ordinal range-normalize a continuous value: 3.14");
		$this->expectException(EncogError::class);
		$column = new ColumnDefinition("A", new ColumnType(ColumnType::continuous));
		$output = [];

		(new RangeOrdinalNormalizer(0, 1))->normalizeColumnDouble($column, 3.14, $output, 0);
	}

	public function testDenormalizeColumn() {
		$normalizer = new RangeOrdinalNormalizer(0, 1);
		$column = new ColumnDefinition("A", new ColumnType(ColumnType::continuous));
		$column->setClasses(["A", "B", "C"]);
		$data = new BasicMLData([0.0, 0.3333333333333333, 0.6666666666666666]);

		$this->assertEquals("A", $normalizer->denormalizeColumn($column, $data, 0));
		$this->assertEquals("B", $normalizer->denormalizeColumn($column, $data, 1));
		$this->assertEquals("C", $normalizer->denormalizeColumn($column, $data, 2));
	}
}
