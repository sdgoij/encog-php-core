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
namespace encog\test\ml\data\buffer;

use encog\ConsoleStatusReportable;
use encog\ml\data\basic\BasicMLData;
use encog\ml\data\basic\BasicMLDataSet;
use encog\ml\data\buffer\codec\CSVDataCODEC;
use encog\ml\data\buffer\codec\DataSetCODEC;
use encog\ml\data\buffer\codec\MLDataSetCODEC;
use encog\ml\data\buffer\MemoryDataLoader;
use encog\ml\data\MLDataPair;
use encog\NullStatusReportable;
use encog\StatusReportable;
use encog\util\csv\CSVFormat;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit_Framework_TestCase as TestCase;

class MemoryDataLoaderTest extends TestCase {
	public function testImport() {
		/** @var MLDataSetCODEC|MockObject $codec */
		$codec = $this->getMockBuilder(MLDataSetCODEC::class)
			->setMethods(['getIdealSize', 'prepareRead', 'read'])
			->disableOriginalConstructor()
			->getMock();
		$codec->expects($this->exactly(3))->method('getIdealSize')
			->will($this->returnValue(1));
		$codec->expects($this->once())->method('prepareRead');
		$codec->expects($this->exactly(4))->method('read')
			->will($this->onConsecutiveCalls(true, true, true, false));

		/** @var StatusReportable|MockObject $status */
		$status = $this->getMockBuilder(NullStatusReportable::class)
			->setMethods(['report'])->getMock();
		$status->expects($this->atLeast(2))->method('report');

		$loader = new MemoryDataLoader($this->createDataCODEC());
		$loader->setCodec($codec);
		$loader->setStatus($status);

		/** @var MLDataPair $pair */
		foreach ($loader->import() as $pair) {
			$this->assertEquals([], $pair->getInputArray());
			$this->assertEquals([], $pair->getIdealArray());
		}

		$loader->setCodec($this->createDataCODEC());
		$loader->setStatus(new NullStatusReportable());
		$loader->setResult(new BasicMLDataSet());

		foreach ($loader->import() as $key => $pair) {
			$this->assertEquals(1+$key, $pair->getInputArray()[0]);
			$this->assertEquals(2+$key, $pair->getInputArray()[1]);
			$this->assertEquals(3+$key, $pair->getIdealArray()[0]);
		}
	}

	public function testCodec() {
		$codec1 = $this->createDataCODEC();
		$codec2 = CSVDataCODEC::reader("foo.csv", CSVFormat::$english, false, 2, 1, false);
		$loader = new MemoryDataLoader($codec1);
		$this->assertEquals($codec1, $loader->getCodec());
		$loader->setCodec($codec2);
		$this->assertEquals($codec2, $loader->getCodec());
	}

	public function testResult() {
		$loader = new MemoryDataLoader($this->createDataCODEC());
		$result = new BasicMLDataSet();
		$this->assertNull($loader->getResult());
		$loader->setResult($result);
		$this->assertEquals($result, $loader->getResult());
	}

	public function testStatus() {
		$console = new ConsoleStatusReportable();
		$loader = new MemoryDataLoader($this->createDataCODEC());
		$this->assertEquals(new NullStatusReportable(), $loader->getStatus());
		$loader->setStatus($console);
		$this->assertEquals($console, $loader->getStatus());
	}

	private function createDataCODEC(): DataSetCODEC {
		return new MLDataSetCODEC(new BasicMLDataSet(
			[
				new BasicMLData([1,2]),
				new BasicMLData([2,3]),
				new BasicMLData([3,4]),
			],
			[
				new BasicMLData([3]),
				new BasicMLData([4]),
				new BasicMLData([5]),
			]
		));
	}
}
