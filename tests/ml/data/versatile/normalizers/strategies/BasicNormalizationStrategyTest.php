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
namespace encog\test\ml\data\versatile\normalizers\strategies;

use encog\EncogError;
use encog\ml\data\versatile\columns\ColumnDefinition;
use encog\ml\data\versatile\columns\ColumnType;
use encog\ml\data\versatile\NormalizationHelper;
use encog\ml\data\versatile\normalizers\OneOfNNormalizer;
use encog\ml\data\versatile\normalizers\RangeNormalizer;
use encog\ml\data\versatile\normalizers\RangeOrdinalNormalizer;
use encog\ml\data\versatile\normalizers\strategies\BasicNormalizationStrategy;
use PHPUnit\Framework\TestCase;

class BasicNormalizationStrategyTest extends TestCase {
	public function testInputNormalizers() {
		$strategy = new BasicNormalizationStrategy(0.0, 1.0, 0.0, 1.0);
		$normalizers = $strategy->getInputNormalizers();

		$this->assertArrayHasKey("continuous", $normalizers);
		$this->assertArrayHasKey("nominal", $normalizers);
		$this->assertArrayHasKey("ordinal", $normalizers);

		$this->assertInstanceOf(RangeNormalizer::class, $normalizers["continuous"]);
		$this->assertInstanceOf(OneOfNNormalizer::class, $normalizers["nominal"]);
		$this->assertInstanceOf(RangeOrdinalNormalizer::class, $normalizers["ordinal"]);
	}

	public function testOutputNormalizers() {
		$strategy = new BasicNormalizationStrategy(0.0, 1.0, 0.0, 1.0);
		$normalizers = $strategy->getOutputNormalizers();

		$this->assertArrayHasKey("continuous", $normalizers);
		$this->assertArrayHasKey("nominal", $normalizers);
		$this->assertArrayHasKey("ordinal", $normalizers);

		$this->assertInstanceOf(RangeNormalizer::class, $normalizers["continuous"]);
		$this->assertInstanceOf(OneOfNNormalizer::class, $normalizers["nominal"]);
		$this->assertInstanceOf(RangeOrdinalNormalizer::class, $normalizers["ordinal"]);
	}

	public function testUnknownColumnType() {
		$strategy = new BasicNormalizationStrategy(0.0, 1.0, 0.0, 1.0);
		$output = [];

		$this->expectExceptionMessage(
			"No normalizer defined for input=1, type=[ColumnDefinition:a(unknown);]");
		$this->expectException(EncogError::class);

		$strategy->normalizeColumn(new ColumnDefinition("a", new ColumnType(0)),
			true, "123", $output, 0);
	}

	public function testNormalizedSize() {
		$strategy = new BasicNormalizationStrategy(0.0, 1.0, 0.0, 1.0);

		$column = new ColumnDefinition("abc", new ColumnType(ColumnType::nominal));
		$this->assertEquals(0, $strategy->normalizedSize($column, true));

		$column->analyze("A");
		$column->analyze("B");
		$column->analyze("C");

		$this->assertEquals(3, $strategy->normalizedSize($column, true));
	}

	public function testNormalizeDouble() {
		$strategy = new BasicNormalizationStrategy(0.0, 1.0, 0.0, 1.0);
		$column = new ColumnDefinition("a", new ColumnType(ColumnType::continuous));
		$column->setOwner(new NormalizationHelper());
		$column->analyze(10.0);
		$column->analyze(0.0);

		$this->assertEquals(10.0, $column->getHigh());
		$this->assertEquals(0.0, $column->getLow());

		$output = [];

		$this->assertEquals(1, $strategy->normalizeColumnDouble($column, false, 5.0, $output, 0));
		$this->assertEquals(0.5, $output[0]);
	}

	public function testDenormalizeColumn() {
		$strategy = new BasicNormalizationStrategy(0.0, 1.0, 0.0, 1.0);
		$column = new ColumnDefinition("a", new ColumnType(ColumnType::nominal));
		$column->setOwner(new NormalizationHelper());
		$column->analyze(10.0);
		$column->analyze(0.0);
	}
}
