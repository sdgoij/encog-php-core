<?php
/**
 * Copyright 2015-2016 Tim Jurcka <sdgoij@gmail.com>
 *
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
use encog\ml\data\versatile\normalizers\PassThroughNormalizer;
use PHPUnit_Framework_TestCase as TestCase;

class PassThroughNormalizerTest extends TestCase {
	public function testOutputSize() {
		$column = new ColumnDefinition("a", new ColumnType(ColumnType::ignore));
		$this->assertEquals(1, (new PassThroughNormalizer())->outputSize($column));
	}

	public function testNormalizeColumn() {
		$this->setExpectedException(EncogError::class, "Can't use a pass-through normalizer on a string value: A");
		$column = new ColumnDefinition("A", new ColumnType(ColumnType::nominal));
		$output = [];
		(new PassThroughNormalizer())->normalizeColumn($column, "A", $output, 0);
	}

	public function testNormalizeColumnDouble() {
		$normalizer = new PassThroughNormalizer();
		$column = new ColumnDefinition("A", new ColumnType(ColumnType::continuous));
		$output = [];

		$this->assertEquals(1, $normalizer->normalizeColumnDouble($column, 1, $output, 0));
		$this->assertEquals(2, $normalizer->normalizeColumnDouble($column, 2, $output, 1));
		$this->assertEquals(3, $normalizer->normalizeColumnDouble($column, 3, $output, 2));
		$this->assertEquals([1,2,3], $output);
	}

	public function testDenormalizeColumn() {
		$column = new ColumnDefinition("A", new ColumnType(ColumnType::continuous));
		$normalizer = new PassThroughNormalizer();
		$data = new BasicMLData([1, 2, 3]);

		$this->assertEquals("1", $normalizer->denormalizeColumn($column, $data, 0));
		$this->assertEquals("2", $normalizer->denormalizeColumn($column, $data, 1));
		$this->assertEquals("3", $normalizer->denormalizeColumn($column, $data, 2));
	}
}
