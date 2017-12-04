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
namespace encog\test\ml\data\versatile;

use encog\EncogError;
use encog\ml\data\basic\BasicMLData;
use encog\ml\data\versatile\columns\ColumnDefinition;
use encog\ml\data\versatile\columns\ColumnType;
use encog\ml\data\versatile\missing\MissingHandler;
use encog\ml\data\versatile\NormalizationHelper;
use encog\ml\data\versatile\normalizers\strategies\BasicNormalizationStrategy;
use encog\ml\data\versatile\normalizers\strategies\NormalizationStrategy;
use encog\util\csv\CSVFormat;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NormalizationHelperTest extends TestCase {
	public function testGetNormStrategy() {
		/** @var NormalizationStrategy|MockObject $normalizer */
		$normalizer = $this->createMock(NormalizationStrategy::class);
		$helper = new NormalizationHelper();

		$this->assertNull($helper->getNormStrategy());

		$helper->setNormStrategy($normalizer);

		$this->assertSame($normalizer, $helper->getNormStrategy());
	}

	public function testDefineUnknownValue() {
		$helper = new NormalizationHelper();
		$helper->setUnknownValues("x", "y");
		$helper->defineUnknownValue("z");

		$this->assertEquals(["x", "y", "z"], $helper->getUnknownValues());

		$helper->setUnknownValues();

		$this->assertEquals([], $helper->getUnknownValues());
	}

	public function testDefineMissingHandler() {
		$helper = new NormalizationHelper();

		/** @var MissingHandler|MockObject $handler */
		$handler = $this->createMock(MissingHandler::class);
		$handler->expects($this->once())
			->method("init")
			->with($helper);

		$helper->defineMissingHandler(
			new ColumnDefinition("a", new ColumnType(ColumnType::continuous)),
			$handler
		);

		$this->assertCount(1, $helper->getMissingHandlers());

		$helper->setMissingHandlers([]);

		$this->assertCount(0, $helper->getMissingHandlers());
	}

	public function testDefineSourceColumn() {
		$helper = new NormalizationHelper();
		$helper->setSourceColumns(
			new ColumnDefinition("a", new ColumnType(ColumnType::continuous))
		);
		$column = $helper->defineSourceColumn("b", 1, new ColumnType(ColumnType::continuous));

		$this->assertSame($helper, $column->getOwner());
		$this->assertCount(2, $helper->getSourceColumns());
		$this->assertEquals("b", $column->getName());
		$this->assertEquals(1, $column->getIndex());

		$helper->setSourceColumns();
		$this->assertEquals([], $helper->getSourceColumns());
	}

	public function testClearInputOutput() {
		$helper = new NormalizationHelper();
		$helper->setInputColumns(new ColumnDefinition("a", new ColumnType(ColumnType::continuous)));
		$helper->setOutputColumns(new ColumnDefinition("b", new ColumnType(ColumnType::continuous)));
		$helper->clearInputOutput();

		$this->assertEquals([], $helper->getInputColumns());
		$this->assertEquals([], $helper->getOutputColumns());
	}

	public function testNormalizeInputColumn() {
		$column = new ColumnDefinition("abc", new ColumnType(ColumnType::continuous));

		/** @var NormalizationStrategy|MockObject $normalizer */
		$normalizer = $this->createMock(NormalizationStrategy::class);
		$normalizer->expects($this->once())
			->method("normalizeColumn")
			->with($column, true, "10", [], 0)
			->willReturnCallback(
				function (ColumnDefinition $column, bool $isInput, string $value, array &$outputData, int $outputColumn): int {
					$outputData[0] = 1;
					return 1;
				})
		;

		$helper = new NormalizationHelper();
		$helper->setNormStrategy($normalizer);
		$helper->setInputColumns($column);

		$this->assertEquals([1], $helper->normalizeInputColumn(0, "10"));
	}

	public function testNormalizeOutputColumn() {
		$column = new ColumnDefinition("abc", new ColumnType(ColumnType::continuous));

		/** @var NormalizationStrategy|MockObject $normalizer */
		$normalizer = $this->createMock(NormalizationStrategy::class);
		$normalizer->expects($this->once())
			->method("normalizeColumn")
			->with($column, true, "10", [], 0)
			->willReturnCallback(
				function (ColumnDefinition $column, bool $isInput, string $value, array &$outputData, int $outputColumn): int {
					$outputData[0] = 1;
					return 1;
				})
		;

		$helper = new NormalizationHelper();
		$helper->setNormStrategy($normalizer);
		$helper->setOutputColumns($column);

		$this->assertEquals([1], $helper->normalizeOutputColumn(0, "10"));
	}

	public function testCalculateNormalizedOutputCount() {
		/** @var NormalizationStrategy|MockObject $normalizer */
		$normalizer = $this->createMock(NormalizationStrategy::class);
		$normalizer->expects($this->exactly(3))
			->method("normalizedSize")
			->willReturn(1);

		$helper = new NormalizationHelper();
		$helper->setNormStrategy($normalizer);

		$helper->setOutputColumns(
			new ColumnDefinition("a", new ColumnType(ColumnType::continuous)),
			new ColumnDefinition("b", new ColumnType(ColumnType::continuous)),
			new ColumnDefinition("c", new ColumnType(ColumnType::continuous))
		);

		$this->assertEquals(3, $helper->calculateNormalizedOutputCount());
	}

	public function testAllocateInputVector() {
		/** @var NormalizationStrategy|MockObject $normalizer */
		$normalizer = $this->createMock(NormalizationStrategy::class);
		$normalizer->expects($this->exactly(6))
			->method("normalizedSize")
			->willReturn(1);

		$helper = new NormalizationHelper();
		$helper->setNormStrategy($normalizer);
		$helper->setInputColumns(
			new ColumnDefinition("a", new ColumnType(ColumnType::continuous)),
			new ColumnDefinition("b", new ColumnType(ColumnType::continuous)),
			new ColumnDefinition("c", new ColumnType(ColumnType::continuous))
		);

		$this->assertEquals(3, $helper->allocateInputVector()->size());
		$this->assertEquals(6, $helper->allocateInputVector(2)->size());
	}

	public function testParseDouble() {
		/** @var CSVFormat|MockObject $format */
		$format = $this->createMock(CSVFormat::class);
		$format->expects($this->once())
			->method("parse")
			->with("3.2")
			->willReturn(3.2);

		$helper = new NormalizationHelper();
		$helper->setFormat($format);

		$this->assertSame(3.2, $helper->parseDouble("3.2"));
	}

	public function testDenormalizeOutputVectorToString() {
		$columns = [
			new ColumnDefinition("a", new ColumnType(ColumnType::continuous)),
			new ColumnDefinition("b", new ColumnType(ColumnType::continuous)),
			new ColumnDefinition("c", new ColumnType(ColumnType::continuous))
		];

		$data = new BasicMLData();

		/** @var NormalizationStrategy|MockObject $normalizer */
		$normalizer = $this->createMock(NormalizationStrategy::class);
		$normalizer->expects($this->exactly(3))
			->method("denormalizeColumn")
			->withConsecutive(
				[$columns[0], false, $data, 0],
				[$columns[1], false, $data, 1],
				[$columns[2], false, $data, 2]
			)
			->willReturn("1", "2", "3");
		$normalizer->expects($this->exactly(3))
			->method("normalizedSize")
			->willReturn(1);

		$helper = new NormalizationHelper();
		$helper->setNormStrategy($normalizer);
		$helper->setOutputColumns(...$columns);

		$this->assertEquals(["1", "2", "3"], $helper->denormalizeOutputVectorToString($data));
	}

	public function testNormalizeToVector() {
		/** @var NormalizationStrategy|MockObject $normalizer */
		$normalizer = $this->createMock(NormalizationStrategy::class);
		$normalizer->expects($this->exactly(2))
			->method("normalizeColumnDouble")
			->willReturnCallback(function (ColumnDefinition $column, bool $isInput, float $value, array &$outputData, int $outputColumn): int {
				$outputData[$outputColumn] = $value;
				return $outputColumn+1;
			});
		$normalizer->expects($this->exactly(2))
			->method("normalizeColumn")
			->willReturnCallback(function (ColumnDefinition $column, bool $isInput, string $value, array &$outputData, int $outputColumn): int {
				$outputData[$outputColumn] = (float)$value;
				return $outputColumn+1;
			});

		/** @var CSVFormat|MockObject $format */
		$format = $this->createMock(CSVFormat::class);
		$format->expects($this->once())
			->method("parse")
			->with("1.11")
			->willReturn(1.11);

		$continuous = new ColumnDefinition("abc", new ColumnType(ColumnType::continuous));
		$ordinal = new ColumnDefinition("def", new ColumnType(ColumnType::ordinal));

		/** @var MissingHandler|MockObject $missing */
		$missing = $this->createMock(MissingHandler::class);
		$missing->expects($this->once())
			->method("processDouble")
			->with($continuous)
			->willReturn(1.11);
		$missing->expects($this->once())
			->method("processString")
			->with($ordinal)
			->willReturn("1.11");

		$helper = new NormalizationHelper();
		$helper->defineMissingHandler($continuous, $missing);
		$helper->defineMissingHandler($ordinal, $missing);
		$helper->defineUnknownValue("x.xx");
		$helper->setNormStrategy($normalizer);
		$helper->setFormat($format);

		$outputs = [];

		$this->assertEquals(1, $helper->normalizeToVector($continuous, 0, $outputs, true, "1.11"));
		$this->assertEquals(2, $helper->normalizeToVector($continuous, 1, $outputs, true, "x.xx"));
		$this->assertEquals(3, $helper->normalizeToVector($ordinal, 2, $outputs, true, "1.11"));
		$this->assertEquals(4, $helper->normalizeToVector($ordinal, 3, $outputs, true, "x.xx"));
		$this->assertEquals([1.11, 1.11, 1.11, 1.11], $outputs);

		$this->expectException(EncogError::class);
		$this->expectExceptionMessage("Do not know how to process missing value \"xyz\" in field: abc");
		$helper = new NormalizationHelper();
		$helper->defineUnknownValue("xyz");
		$helper->normalizeToVector(
			new ColumnDefinition("abc", new ColumnType(ColumnType::ordinal)),
			0,
			$outputs,
			true,
			"xyz"
		);
	}

	public function testNormalizeInputVector() {
		$helper = new NormalizationHelper();
		$helper->setNormStrategy(new BasicNormalizationStrategy(0.0, 1.0, 0.0, 1.0));
		$helper->setInputColumns(
			new ColumnDefinition("a", new ColumnType(ColumnType::continuous)),
			new ColumnDefinition("b", new ColumnType(ColumnType::continuous)),
			new ColumnDefinition("c", new ColumnType(ColumnType::continuous))
		);
		/** @var ColumnDefinition $column */
		foreach ($helper->getInputColumns() as $column) {
			foreach (["0.0", "5.0", "10.0"] as $value) {
				$column->setOwner($helper);
				$column->analyze($value);
			}
		}
		$output = [];
		$helper->normalizeInputVector(["0.0", "5.0", "10.0"], $output, false);
		$this->assertEquals([0.0, 0.5, 1.0], $output);
	}
}
