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
namespace encog\ml\data\buffer\codec;

use encog\ml\data\buffer\BufferedDataError;
use encog\util\csv\CSVFormat;
use encog\util\csv\CSVReader;
use encog\util\csv\NumberList;

/**
 * A CODEC used to read/write data from/to a CSV data file. There are two
 * constructors provided, one is for reading, the other for writing. Make sure
 * you use the correct one for your intended purpose.
 *
 * This CODEC is typically used with the BinaryDataLoader, to load external data
 * into the Encog binary training format.
 */
class CSVDataCODEC implements DataSetCODEC {
	private $filename;
	private $format;
	private $input = -1;
	private $ideal = -1;
	private $headers = false;
	private $significance = false;
	/** @var CSVReader */
	private $reader;
	private $writer;

	public function __construct() {
		$this->format = CSVFormat::$english;
	}

	public static function fromCSVReader(CSVReader $reader, int $input, int $ideal, bool $significance): CSVDataCODEC {
		$codec = new static();
		$codec->significance = $significance;
		$codec->reader = $reader;
		$codec->input = $input;
		$codec->ideal = $ideal;
		return $codec;
	}

	public static function reader(string $filename, CSVFormat $format, bool $headers,
			int $input, int $ideal, bool $significance): CSVDataCODEC {
		$codec = new static();
		$codec->filename = $filename;
		$codec->format = $format;
		$codec->input = $input;
		$codec->ideal = $ideal;
		$codec->headers = $headers;
		$codec->significance = $significance;
		return $codec;
	}

	public static function writer(string $filename, CSVFormat $format, bool $significance): CSVDataCODEC {
		$codec = new static();
		$codec->filename = $filename;
		$codec->format = $format;
		$codec->significance = $significance;
		return $codec;
	}

	public function read(array &$input, array &$ideal, float &$significance): bool {
		if ($this->reader && $this->reader->next()) {
			$index = 0;
			for ($i = 0; $i < $this->input; $i++) {
				$input[$i] = $this->reader->getDouble($index++);
			}
			for ($i = 0; $i < $this->ideal; $i++) {
				$ideal[$i] = $this->reader->getDouble($index++);
			}
			$significance = $this->significance
				? $this->reader->getDouble($index)
				: 1.0;
			return true;
		}
		return false;
	}

	public function write(array $input, array $ideal, float $significance) {
		$record = array_merge($input, $ideal);
		if ($this->significance) {
			$record[] = $significance;
		}
		$line = NumberList::toList($this->format, $record) . "\r\n";
		fwrite($this->writer, $line, strlen($line));
	}

	public function prepareWrite(int $records, int $input, int $ideal) {
		$this->input = $input;
		$this->ideal = $ideal;
		if (!$this->writer = @fopen($this->filename, "w")) {
			throw new BufferedDataError("Unable to open '{$this->filename}'");
		}
	}

	public function prepareRead() {
		if (!$this->reader) {
			if ($this->input == -1) {
				throw new BufferedDataError(
					"To import CSV, you must use a constructor that specifies input and ideal sizes."
				);
			}
			$this->reader = CSVReader::createFromFileName(
				$this->filename, $this->headers, $this->format
			);
		}
	}

	public function getInputSize(): int {
		return $this->input;
	}

	public function getIdealSize(): int {
		return $this->ideal;
	}

	public function close() {
		if ($this->reader) {
			$this->reader->close();
			$this->reader = null;
		}
		if ($this->writer) {
			fclose($this->writer);
			$this->writer = null;
		}
	}
}
