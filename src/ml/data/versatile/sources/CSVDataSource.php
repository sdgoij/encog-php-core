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
namespace encog\ml\data\versatile\sources;

use encog\EncogError;
use encog\util\csv\CSVFormat;
use encog\util\csv\CSVReader;

/**
 * Allow a CSV file to serve as a source for the versatile data set.
 */
class CSVDataSource implements VersatileDataSource {
	/** @var CSVReader */
	private $reader;
	/** @var string */
	private $file;
	/** @var bool */
	private $headers;
	/** @var CSVFormat */
	private $format;
	/** @var int[] */
	private $headerIndex = [];

	public function __construct(string $file, bool $headers, $format) {
		if (!$format instanceof CSVFormat) {
			$format = new CSVFormat(CSVFormat::getDecimalCharacter(), $format);
		}
		$this->file = $file;
		$this->headers = $headers;
		$this->format = $format;
	}

	public function columnIndex(string $name): int {
		return $this->headerIndex[strtolower($name)] ?? -1;
	}

	public function readLine(): array {
		if (!$this->reader) {
			throw new EncogError("Please call rewind before reading the file.");
		}
		if ($this->reader->next()) {
			$columns = count($this->reader->getColumnNames());
			for ($i = 0, $v = []; $i < $columns; $i++) {
				$v[$i] = $this->reader->get($i);
			}
			return $v;
		}
		$this->reader->close();
		$this->reader = null;
		return [];
	}

	public function rewind() {
		$this->reader = CSVReader::createFromFileName($this->file, $this->headers, $this->format);
		if (!count($this->headerIndex)) {
			foreach ($this->reader->getColumnNames() as $column => $name) {
				$this->headerIndex[$name] = $column;
			}
		}
	}
}
