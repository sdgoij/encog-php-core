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
namespace encog\ml\data\buffer;

use SplFileInfo;
use SplFileObject;

/**
 * Used to access or create an Encog Binary Training file (*.EGB).
 */
class EncogEGBFile {

	const DOUBLE_SIZE = 8;
	const HEADER_SIZE = self::DOUBLE_SIZE * 3;

	public function __construct(SplFileInfo $file) {
		if (!$file instanceof SplFileObject) {
			$file = $file->openFile("c+b");
		}
		$this->file = $file;
	}

	public function __destruct() {
		if ($this->file) {
			$this->file->fflush();
			$this->file = null;
		}
	}

	public function create(int $input, int $ideal) {
		if (!$this->file->ftruncate(0)) {
			throw new BufferedDataError("Unable to truncate file.");
		}
		$this->file->rewind();
		assert(24 == $this->file->fwrite(pack("c8d2",
			ord('E'),
			ord('N'),
			ord('C'),
			ord('O'),
			ord('G'),
			ord('-'),
			ord('0'),
			ord('0'),
			$input,
			$ideal
		)));
		$this->recordValues = $input + $ideal + 1;
		$this->recordSize = $this->recordValues * self::DOUBLE_SIZE;
		$this->numRecords = 0;
		$this->input = $input;
		$this->ideal = $ideal;
	}

	public function open() {
		$header = (false !== $data = @unpack("c8id/dinput/dideal", $this->file->fread(24)))
			? array_values($data)
			: array_fill(0, 8, 0)
		;
		$version = chr($header[6]) . chr($header[7]);
		$isEncogFile =
			chr($header[0]) == 'E' &&
			chr($header[1]) == 'N' &&
			chr($header[2]) == 'C' &&
			chr($header[3]) == 'O' &&
			chr($header[4]) == 'G' &&
			chr($header[5]) == '-'
		;
		if (!$isEncogFile) {
			throw new BufferedDataError("File is not a valid Encog binary file: {$this->file->getFilename()}");
		}
		if (!is_numeric($version)) {
			throw new BufferedDataError("File has invalid version number.");
		}
		if (intval($version) > 0) {
			throw new BufferedDataError("File is from a newer version of Encog than is currently in use.");
		}

		$this->input = (int)$header[8];
		$this->ideal = (int)$header[9];
		$this->recordValues = $this->input + $this->ideal + 1;
		$this->recordSize = $this->recordValues * self::DOUBLE_SIZE;
		$this->numRecords = $this->recordSize > 0
			? (int)(($this->size()-self::HEADER_SIZE) / $this->recordSize) : 0;
	}

	public function getInputCount(): int {
		return $this->input;
	}

	public function getIdealCount(): int {
		return $this->ideal;
	}

	public function getNumberOfRecords(): int {
		return $this->numRecords;
	}

	public function getNumberOfRecordValues(): int {
		return $this->recordValues;
	}

	public function getRecordSize(): int {
		return $this->recordSize;
	}

	public function read(): float {
		return unpack("e", $this->file->fread(self::DOUBLE_SIZE))[1];
	}

	public function readArray(array &$data) {
		$data = array_values(unpack("e*", $this->file->fread(count($data))));
	}

	public function readColumn(int $row, int $column): float {
		return $this->seek($row, $column)->read();
	}

	public function readRow(int $row): array {
		$data = array_fill(0, $this->recordSize-1, 0.0);
		$this->seek($row, 0)->readArray($data);
		return $data;
	}

	public function write(float $value) {
		$this->file->fwrite(pack("e", $value));
	}

	public function writeArray(array $values) {
		$this->file->fwrite(pack("e*", ...$values));
	}

	public function writeByte(int $value) {
		$this->file->fwrite(pack("c", $value));
	}

	public function writeColumn(int $row, int $column, float $value) {
		$this->seek($row, $column)->write($value);
	}

	public function writeRow(int $row, array $values) {
		$this->seek($row, 0)->writeArray($values);
	}

	private function seek(int $row, int $column): self {
		$offset = self::HEADER_SIZE + ($row * $this->recordSize) + ($column * self::DOUBLE_SIZE);
		$size = $this->size();
		if ($offset > $size) {
			$n = $offset-$size;
			$this->file->fseek(0, SEEK_END);
			$this->file->fwrite(pack("x$n"));
		} else {
			$this->file->fseek($offset);
		}
		return $this;
	}

	private function size(): int {
		$pos = $this->file->ftell();
		$this->file->fseek(0, SEEK_END);
		$size = $this->file->ftell();
		$this->file->fseek($pos);
		return $size;
	}

	/** @var SplFileObject|null */
	private $file;

	/** @var int */
	private $input;

	/** @var int */
	private $ideal;

	/** @var int */
	private $recordValues;

	/** @var int */
	private $recordSize;

	/** @var int */
	private $numRecords;
}
