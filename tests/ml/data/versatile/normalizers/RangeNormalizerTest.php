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
use encog\ml\data\versatile\normalizers\RangeNormalizer;
use PHPUnit_Framework_TestCase as TestCase;

class RangeNormalizerTest extends TestCase {
	public function testOutputSize() {
		$column = new ColumnDefinition("a", new ColumnType(ColumnType::ignore));
		$this->assertEquals(1, (new RangeNormalizer(0, 1))->outputSize($column));
	}

	public function testNormalizeColumn() {
		$this->setExpectedException(EncogError::class, "Can't range-normalize a string value: A");
		$column = new ColumnDefinition("a", new ColumnType(ColumnType::nominal));
		$output = [];

		(new RangeNormalizer(0, 1))->normalizeColumn($column, "A", $output, 0);
	}

	public function testNormalizeColumnDouble() {
		$normalizer = new RangeNormalizer(0, 1);
		$column = new ColumnDefinition("A", new ColumnType(ColumnType::continuous));
		$output = [];

		$this->assertEquals(1, $normalizer->normalizeColumnDouble($column, -1, $output, 0));
		$this->assertEquals(2, $normalizer->normalizeColumnDouble($column, 0, $output, 1));
		$this->assertEquals(3, $normalizer->normalizeColumnDouble($column, 1, $output, 2));
		$this->assertEquals([0.5, 0.5, 0.5], $output);
	}

	public function testDenormalizeColumn() {
		$normalizer = new RangeNormalizer(0, 1);
		$column = new ColumnDefinition("A", new ColumnType(ColumnType::continuous));
		$data = new BasicMLData([-1, 0, 1]);

		$this->assertEquals("0.5", $normalizer->denormalizeColumn($column, $data, 0));
		$this->assertEquals("0.5", $normalizer->denormalizeColumn($column, $data, 1));
		$this->assertEquals("0.5", $normalizer->denormalizeColumn($column, $data, 2));
	}
}
