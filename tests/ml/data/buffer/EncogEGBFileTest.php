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
namespace encog\test\ml\data\buffer;

use encog\ml\data\buffer\BufferedDataError;
use encog\ml\data\buffer\EncogEGBFile;
use encog\test\util\csv\MemoryStream;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use SplFileInfo;
use SplFileObject;

class EncogEGBFileTest extends TestCase {
	public function testOpenFile() {
		$this->markTestIncomplete("Return value for SplFileInfo::openFile() cannot be generated: The parent constructor was not called: the object is in an invalid state");
		/** @var MockObject|SplFileInfo */
		$file = $this->getMockBuilder(SplFileInfo::class)->disableOriginalConstructor()->getMock(); // @phpstan-ignore deadCode.unreachable
		$file->expects($this->once())->method("openFile");
		new EncogEGBFile($file);

		$this->expectExceptionMessage("failed to open stream: No such file or directory");
		$this->expectException(RuntimeException::class);
		new EncogEGBFile(new SplFileInfo(sys_get_temp_dir()."/xxx/yyy/non-existing.egb"));
	}

	public function testCreateEGB() {
		MemoryStream::register("egb");
		$expect = pack("c8d2",
			ord('E'),
			ord('N'),
			ord('C'),
			ord('O'),
			ord('G'),
			ord('-'),
			ord('0'),
			ord('0'),
			1, 1
		);
		$egb = new EncogEGBFile(new SplFileInfo("egb://create"));
		$egb->create(1, 1);

		$this->assertEquals($expect, file_get_contents("egb://create"));
		$this->assertEquals(0, $egb->getNumberOfRecords());
		$this->assertEquals(3, $egb->getNumberOfRecordValues());
		$this->assertEquals(24, $egb->getRecordSize());
		$this->assertEquals(1, $egb->getInputCount());
		$this->assertEquals(1, $egb->getIdealCount());

		$this->expectExceptionMessage("Unable to truncate file.");
		$this->expectException(BufferedDataError::class);

		/** @var MockObject|SplFileInfo */
		$file = $this->getMockBuilder(SplFileObject::class)->setConstructorArgs(["php://output"])->getMock();
		$file->expects($this->once())->method('ftruncate')->willReturn(false);
		(new EncogEGBFile($file))->create(1, 1);
	}

	public function testOpenEGB() {
		MemoryStream::put("open", base64_decode(
			"RU5DT0ctMDAAAAAAAAAAQAAAAAAAAPA/AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAPA/AAAAAAAA8D8AAAAAAAAAAAAAAAAAAPA/AAAAAAAAAAAAAAAAAADwPwAAAAAAAAAAAAAAAAAA8D8AAAAAAADwPwAAAAAAAAAA"
		), "egb");

		$egb = new EncogEGBFile(new SplFileInfo("egb://open"));
		$egb->open();

		$this->assertEquals(2, $egb->getInputCount());
		$this->assertEquals(1, $egb->getIdealCount());
		$this->assertEquals(32, $egb->getRecordSize());
		$this->assertEquals(4, $egb->getNumberOfRecordValues());
		$this->assertEquals(3, $egb->getNumberOfRecords());
	}

	public function testOpenInvalidHeader() {
		MemoryStream::put("open", "abc", "egb");
		$this->expectException(BufferedDataError::class);
		$this->expectExceptionMessageMatches("/File is not a valid Encog binary file: .*/");
		(new EncogEGBFile(new SplFileInfo("egb://open")))->open();
	}

	public function testOpenInvalidVersionNumber() {
		MemoryStream::put("open", pack("c8d2",
			ord('E'),
			ord('N'),
			ord('C'),
			ord('O'),
			ord('G'),
			ord('-'),
			ord('x'),
			ord('y'),
			1, 1
		), "egb");

		$this->expectException(BufferedDataError::class);
		$this->expectExceptionMessage("File has invalid version number.");
		(new EncogEGBFile(new SplFileInfo("egb://open")))->open();
	}

	public function testOpenNewerVersionNumber() {
		MemoryStream::put("open", pack("c8d2",
			ord('E'),
			ord('N'),
			ord('C'),
			ord('O'),
			ord('G'),
			ord('-'),
			ord('1'),
			ord('0'),
			1, 1
		), "egb");

		$this->expectException(BufferedDataError::class);
		$this->expectExceptionMessage("File is from a newer version of Encog than is currently in use.");
		(new EncogEGBFile(new SplFileInfo("egb://open")))->open();
	}

	public function testReadColumn() {
		MemoryStream::put("open", base64_decode(
			"RU5DT0ctMDAAAAAAAAAAQAAAAAAAAPA/AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAPA/AAAAAAAA8D8AAAAAAAAAAAAAAAAAAPA/AAAAAAAAAAAAAAAAAADwPwAAAAAAAAAAAAAAAAAA8D8AAAAAAADwPwAAAAAAAAAA"
		), "egb");

		$egb = new EncogEGBFile(new SplFileInfo("egb://open"));
		$egb->open();

		$this->assertEquals(0, $egb->readColumn(0, 0));
		$this->assertEquals(0, $egb->readColumn(0, 1));
		$this->assertEquals(0, $egb->readColumn(0, 2));

		$this->assertEquals(0, $egb->readColumn(1, 0));
		$this->assertEquals(1, $egb->readColumn(1, 1));
		$this->assertEquals(1, $egb->readColumn(1, 2));

		$this->assertEquals(1, $egb->readColumn(2, 0));
		$this->assertEquals(0, $egb->readColumn(2, 1));
		$this->assertEquals(1, $egb->readColumn(2, 2));

		$this->assertEquals(1, $egb->readColumn(3, 0));
		$this->assertEquals(1, $egb->readColumn(3, 1));
		$this->assertEquals(0, $egb->readColumn(3, 2));
	}

	public function testReadRow() {
		MemoryStream::put("open", base64_decode(
			"RU5DT0ctMDAAAAAAAAAAQAAAAAAAAPA/AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAPA/AAAAAAAA8D8AAAAAAAAAAAAAAAAAAPA/AAAAAAAAAAAAAAAAAADwPwAAAAAAAAAAAAAAAAAA8D8AAAAAAADwPwAAAAAAAAAA"
		), "egb");

		$egb = new EncogEGBFile(new SplFileInfo("egb://open"));
		$egb->open();

		$this->assertEquals([0,0,0], $egb->readRow(0));
		$this->assertEquals([0,1,1], $egb->readRow(1));
		$this->assertEquals([1,0,1], $egb->readRow(2));
		$this->assertEquals([1,1,0], $egb->readRow(3));
	}


	public function testWrite() {
		MemoryStream::register("egb");

		$file = new SplFileInfo("egb://write");
		$egb = new EncogEGBFile($file);
		$egb->create(2, 1);
		$egb->write(1);
		$egb->write(2);
		$egb->write(3);

		$this->assertEquals(
			"RU5DT0ctMDAAAAAAAAAAQAAAAAAAAPA/AAAAAAAA8D8AAAAAAAAAQAAAAAAAAAhA",
			base64_encode(file_get_contents("egb://write"))
		);
	}

	public function testWriteByte() {
		MemoryStream::register("egb");

		$file = new SplFileInfo("egb://write-byte");
		$egb = new EncogEGBFile($file);
		$egb->create(2, 1);
		$egb->writeByte(ord('a'));
		$egb->writeByte(ord('n'));
		$egb->writeByte(ord('u'));

		$this->assertEquals(
			"RU5DT0ctMDAAAAAAAAAAQAAAAAAAAPA/YW51",
			base64_encode(file_get_contents("egb://write-byte"))
		);
	}

	public function testWriteArray() {
		MemoryStream::register("egb");

		$file = new SplFileInfo("egb://write-array");
		$egb = new EncogEGBFile($file);
		$egb->create(2, 1);
		$egb->writeArray([1,2,3]);
		$this->assertEquals(
			"RU5DT0ctMDAAAAAAAAAAQAAAAAAAAPA/AAAAAAAA8D8AAAAAAAAAQAAAAAAAAAhA",
			base64_encode(file_get_contents("egb://write-array"))
		);
	}

	public function testWriteColumn() {
		MemoryStream::register("egb");

		$file = new SplFileInfo("egb://write-column");
		$egb = new EncogEGBFile($file);
		$egb->create(2, 1);

		$egb->writeColumn(2, 2, 3);
		$egb->writeColumn(1, 1, 2);
		$egb->writeColumn(0, 0, 1);

		$this->assertEquals(
			"RU5DT0ctMDAAAAAAAAAAQAAAAAAAAPA/AAAAAAAA8D8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAIQA==",
			base64_encode(file_get_contents("egb://write-column"))
		);
	}

	public function testWriteRow() {
		MemoryStream::register("egb");

		$file = new SplFileInfo("egb://write-row");
		$egb = new EncogEGBFile($file);
		$egb->create(2, 1);
		$egb->writeRow(3, [1,2,3]);
		$egb->writeRow(1, [3,2,1]);
		$this->assertEquals(
			"RU5DT0ctMDAAAAAAAAAAQAAAAAAAAPA/AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAIQAAAAAAAAABAAAAAAAAA8D8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA8D8AAAAAAAAAQAAAAAAAAAhA",
			base64_encode(file_get_contents("egb://write-row"))
		);
	}
}
