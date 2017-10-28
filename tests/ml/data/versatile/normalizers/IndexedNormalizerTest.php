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
use encog\ml\data\versatile\normalizers\IndexedNormalizer;
use PHPUnit\Framework\TestCase;

class IndexedNormalizerTest extends TestCase {
	public function testOutputSize() {
		$this->assertEquals(1, (new IndexedNormalizer())->outputSize(
			new ColumnDefinition("a", new ColumnType(ColumnType::ignore)
		)));
	}

	public function testNormalizeColumn() {
		$column = new ColumnDefinition("A", new ColumnType(ColumnType::nominal));
		$column->setClasses(["A", "B", "C"]);
		$normalizer = new IndexedNormalizer();
		$output = [];

		$this->assertEquals(1, $normalizer->normalizeColumn($column, "A", $output, 0));
		$this->assertEquals(2, $normalizer->normalizeColumn($column, "B", $output, 1));
		$this->assertEquals(3, $normalizer->normalizeColumn($column, "C", $output, 2));
		$this->assertEquals([0, 1, 2], $output);

		$this->expectException(EncogError::class);
		$this->expectExceptionMessage("Undefined value: D");
		$normalizer->normalizeColumn($column, "D", $output, 0);
	}

	public function testNormalizeColumnDouble() {
		$this->expectException(EncogError::class);
		$this->expectExceptionMessage("Can't use an indexed normalizer on a continuous value: 3.14");
		$column = new ColumnDefinition("A", new ColumnType(ColumnType::continuous));
		$output = [];

		(new IndexedNormalizer())->normalizeColumnDouble($column, 3.14, $output, 0);
	}

	public function testDenormalizeColumn() {
		$data = new BasicMLData([0, 1, 2]);
		$column = new ColumnDefinition("A", new ColumnType(ColumnType::nominal));
		$column->setClasses(["A", "B", "C"]);
		$normalizer = new IndexedNormalizer();

		$this->assertEquals("A", $normalizer->denormalizeColumn($column, $data, 0));
		$this->assertEquals("B", $normalizer->denormalizeColumn($column, $data, 1));
		$this->assertEquals("C", $normalizer->denormalizeColumn($column, $data, 2));
	}
}
