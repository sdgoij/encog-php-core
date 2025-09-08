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
namespace encog\test\ml\data\versatile;

use encog\EncogError;
use encog\mathutil\randomize\generate\LinearCongruentialRandom;
use encog\ml\data\versatile\columns\ColumnDefinition;
use encog\ml\data\versatile\columns\ColumnType;
use encog\ml\data\versatile\division\DataDivision;
use encog\ml\data\versatile\NormalizationHelper;
use encog\ml\data\versatile\normalizers\strategies\BasicNormalizationStrategy;
use encog\ml\data\versatile\sources\CSVDataSource;
use encog\ml\data\versatile\sources\VersatileDataSource;
use encog\ml\data\versatile\VersatileMLDataSet;
use encog\test\util\csv\MemoryStream;
use encog\util\csv\CSVFormat;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class VersatileMLDataSetTest extends TestCase {
	public function testAnalyse() {
		/** @var VersatileDataSource|MockObject $source */
		$source = $this->createMock(VersatileDataSource::class);
		$source->expects($this->exactly(4))
			->method("readLine")
			->willReturn(
				["1","1","1"], [],
				["1","1","1"], []
			);
		$source->expects($this->exactly(2))
			->method("rewind");
		$source->expects($this->exactly(3))
			->method("columnIndex")
			->willReturnMap([
				["a", 0],
				["b", 1],
				["c", 2],
			]);

		/**
		 * @var ColumnDefinition|MockObject $a
		 * @var ColumnDefinition|MockObject $b
		 * @var ColumnDefinition|MockObject $c
		 */
		$a = $this->createMock(ColumnDefinition::class);
		$b = $this->createMock(ColumnDefinition::class);
		$c = $this->createMock(ColumnDefinition::class);

		$a->expects($this->exactly(2))
			->method("getIndex")
			->willReturn(-1, 0);
		$a->expects($this->once())
			->method("setIndex")
			->with(0);
		$a->expects($this->once())
			->method("getName")
			->willReturn("a");
		$a->expects($this->any())
			->method("getType")
			->willReturn(new ColumnType(ColumnType::continuous));
		$a->expects($this->exactly(2))
			->method("getCount")
			->willReturn(1);
		$a->expects($this->exactly(2))
			->method("getMean")
			->willReturn(1.0);
		$a->expects($this->once())
			->method("setMean");
		$a->expects($this->exactly(2))
			->method("getSd")
			->willReturn(1.0);
		$a->expects($this->exactly(3))
			->method("setSd");

		$b->expects($this->exactly(2))
			->method("getIndex")
			->willReturn(-1, 1);
		$b->expects($this->once())
			->method("setIndex")
			->with(1);
		$b->expects($this->once())
			->method("getName")
			->willReturn("b");
		$b->expects($this->any())
			->method("getType")
			->willReturn(new ColumnType(ColumnType::continuous));
		$b->expects($this->exactly(2))
			->method("getCount")
			->willReturn(1);
		$b->expects($this->exactly(2))
			->method("getMean")
			->willReturn(1.0);
		$b->expects($this->once())
			->method("setMean");
		$b->expects($this->exactly(2))
			->method("getSd")
			->willReturn(1.0);
		$b->expects($this->exactly(3))
			->method("setSd");

		$c->expects($this->exactly(2))
			->method("getIndex")
			->willReturn(-1, 2);
		$c->expects($this->once())
			->method("setIndex")
			->with(2);
		$c->expects($this->once())
			->method("getName")
			->willReturn("c");
		$c->expects($this->any())
			->method("getType")
			->willReturn(new ColumnType(ColumnType::continuous));
		$c->expects($this->exactly(2))
			->method("getCount")
			->willReturn(1);
		$c->expects($this->exactly(2))
			->method("getMean")
			->willReturn(1.0);
		$c->expects($this->once())
			->method("setMean");
		$c->expects($this->exactly(2))
			->method("getSd")
			->willReturn(1.0);
		$c->expects($this->exactly(3))
			->method("setSd");

		/** @var NormalizationHelper|MockObject $normalizer */
		$normalizer = $this->createMock(NormalizationHelper::class);
		$normalizer->expects($this->any())
			->method("getSourceColumns")
			->willReturn([$a, $b, $c]);
		$normalizer->expects($this->any())
			->method("parseDouble")
			->willReturnCallback(function (string $value): float {
				return (float)$value;
			});

		$dataset = new VersatileMLDataSet($source);
		$dataset->setHelper($normalizer);
		$dataset->analyze();

		$this->expectException(EncogError::class);
		$this->expectExceptionMessage("Can't find column");
		/** @var VersatileDataSource|MockObject $source */
		$source = $this->createMock(VersatileDataSource::class);
		$source->expects($this->once())->method("columnIndex")->willReturn(-1);
		$source->expects($this->once())->method("readLine")->willReturn(["1","1","1"]);
		$source->expects($this->once())->method("rewind");
		/** @var ColumnDefinition|MockObject $column */
		$column = $this->createMock(ColumnDefinition::class);
		$column->expects($this->any())->method("getIndex")->willReturn(-1);
		$column->expects($this->any())->method("getName")->willReturn("");
		$normalizer = new NormalizationHelper();
		$normalizer->addSourceColumn($column);
		$dataset = new VersatileMLDataSet($source);
		$dataset->setHelper($normalizer);
		$dataset->analyze();
	}

	public function testNormalize() {
		/** @var VersatileDataSource|MockObject $source */
		$source = $this->createMock(VersatileDataSource::class);
		$source->expects($this->exactly(2))
			->method("readLine")
			->willReturn(["1", "1", "1"], []);
		$source->expects($this->once())
			->method("rewind");

		/** @var ColumnDefinition|MockObject $column1 */
		$column1 = $this->createMock(ColumnDefinition::class);
		$column1->expects($this->any())
			->method("getIndex")
			->willReturn(0);
		/** @var ColumnDefinition|MockObject $column2 */
		$column2 = $this->createMock(ColumnDefinition::class);
		$column2->expects($this->any())
			->method("getIndex")
			->willReturn(1);
		/** @var ColumnDefinition|MockObject $column3 */
		$column3 = $this->createMock(ColumnDefinition::class);
		$column3->expects($this->any())
			->method("getIndex")
			->willReturn(2);

		/** @var NormalizationHelper|MockObject $helper */
		$helper = $this->createMock(NormalizationHelper::class);
		$helper->expects($this->once())
			->method("calculateNormalizedInputCount")
			->willReturn(2);
		$helper->expects($this->once())
			->method("calculateNormalizedOutputCount")
			->willReturn(1);
		$helper->expects($this->once())
			->method("getInputColumns")
			->willReturn([$column1, $column2]);
		$helper->expects($this->once())
			->method("getOutputColumns")
			->willReturn([$column3]);
		$helper->expects($this->once())
			->method("getNormStrategy")
			->willReturn(new BasicNormalizationStrategy(0, 1, 0, 1));
		$helper->expects($this->exactly(3))
			->method("normalizeToVector")
			->willReturnCallback(
				function (ColumnDefinition $column, int $outputColumn, array &$output, bool $isInput, string $value): int {
					$output[$outputColumn] = (float)$value;
					return $outputColumn+1;
				}
			);

		$dataset = new VersatileMLDataSet($source);
		$analyzed = (new \ReflectionObject($dataset))->getProperty("analyzed");
		$analyzed->setAccessible(true);
		$analyzed->setValue($dataset, 1);
		$analyzed->setAccessible(false);

		$dataset->setHelper($helper);
		$dataset->normalize();

		$this->assertEquals([[1.0, 1.0, 1.0]], $dataset->getData());

		$this->expectException(EncogError::class);
		$this->expectExceptionMessage("Please choose a model type first, with selectMethod.");
		/** @var VersatileDataSource|MockObject $source */
		$source = $this->createMock(VersatileDataSource::class);
		(new VersatileMLDataSet($source))->normalize();
	}

	public function testDivide() {
		MemoryStream::put("memory://data.csv", join("\r\n", [
			"a,b,c",
			"1,2,3",
			"4,5,6",
			"7,8,9",
		]));
		$dataset = new VersatileMLDataSet(new CSVDataSource("memory://data.csv", true, CSVFormat::English()));
		$dataset->getHelper()->setNormStrategy(new BasicNormalizationStrategy(0,1,0,1));

		$columns[] = $dataset->defineSourceColumn("a", new ColumnType(ColumnType::continuous), 0);
		$columns[] = $dataset->defineSourceColumn("b", new ColumnType(ColumnType::continuous), 1);
		$columns[] = $dataset->defineSourceColumn("c", new ColumnType(ColumnType::continuous), 2);

		$dataset->defineInput($columns[0]);
		$dataset->defineInput($columns[1]);
		$dataset->defineOutput($columns[2]);
		$dataset->analyze();
		$dataset->normalize();

		$random = new LinearCongruentialRandom(1);
		/** @var DataDivision[] $divisions */
		$divisions = [
			new DataDivision(1/3),
			new DataDivision(1/3),
			new DataDivision(1/3),
		];

		$dataset->divide($divisions, false, $random);

		$this->assertEquals([0.0, 0.0, 0.0], $divisions[0]->getDataSet()->getData()[0]);
		$this->assertEquals([0.5, 0.5, 0.5], $divisions[1]->getDataSet()->getData()[1]);
		$this->assertEquals([1.0, 1.0, 1.0], $divisions[2]->getDataSet()->getData()[2]);

		$this->expectException(EncogError::class);
		$this->expectExceptionMessage("Can't divide, data has not yet been generated/normalized.");
		/** @var VersatileDataSource|MockObject $source */
		$source = $this->createMock(VersatileDataSource::class);
		(new VersatileMLDataSet($source))->divide(
			$divisions, false, $random
		);
	}

	public function testDefineSingleOutputOthersInput() {
		/** @var VersatileDataSource|MockObject $source */
		$source = $this->createMock(VersatileDataSource::class);

		$dataset = new VersatileMLDataSet($source);
		$output = $dataset->defineSourceColumn("a", new ColumnType(ColumnType::continuous));
		$dataset->defineSourceColumn("b", new ColumnType(ColumnType::continuous));
		$dataset->defineSourceColumn("c", new ColumnType(ColumnType::continuous));
		$dataset->defineSingleOutputOthersInput($output);

		$this->assertCount(2, $dataset->getHelper()->getInputColumns());
		$this->assertCount(1, $dataset->getHelper()->getOutputColumns());
	}

	public function testDefineMultipleOutputsOthersInput() {
		/** @var VersatileDataSource|MockObject $source */
		$source = $this->createMock(VersatileDataSource::class);

		$dataset = new VersatileMLDataSet($source);
		$outputs[] = $dataset->defineSourceColumn("a", new ColumnType(ColumnType::continuous));
		$outputs[] = $dataset->defineSourceColumn("b", new ColumnType(ColumnType::continuous));
		$dataset->defineSourceColumn("c", new ColumnType(ColumnType::continuous));
		$dataset->defineMultipleOutputsOthersInput($outputs);

		$this->assertCount(1, $dataset->getHelper()->getInputColumns());
		$this->assertCount(2, $dataset->getHelper()->getOutputColumns());
	}

	public function testSetSource() {
		/** @var VersatileDataSource|MockObject $source */
		$source = $this->createMock(VersatileDataSource::class);
		$dataset = new VersatileMLDataSet($source);

		$this->assertSame($source, $dataset->getSource());

		/** @var VersatileDataSource|MockObject $other */
		$other = $this->createMock(VersatileDataSource::class);
		$dataset->setSource($other);

		$this->assertNotSame($source, $other);
		$this->assertNotSame($source, $dataset->getSource());
		$this->assertSame($other, $dataset->getSource());
	}
}
