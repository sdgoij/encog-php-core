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
namespace encog\ml\data\specific;

use encog\ml\data\basic\BasicMLDataSet;
use encog\ml\data\buffer\codec\CSVDataCODEC;
use encog\ml\data\buffer\MemoryDataLoader;
use encog\util\csv\CSVFormat;

/**
 * An implementation of the MLDataSet interface designed to provide a CSV
 * file to the neural network. This implementation uses the BasicMLData to
 * hold the data being read. This class has no ability to write CSV files. The
 * columns of the CSV file will specify both the input and ideal columns.
 *
 * Because this class loads the CSV file to memory, it is quite fast, once the
 * data has been loaded.
 */
class CSVMLDataSet extends BasicMLDataSet {
	public function __construct(string $filename, int $input, int $ideal, bool $headers,
			CSVFormat $format = null, bool $significance = false) {
		parent::__construct();
		$this->filename = $filename;
		$this->format = $format ?? CSVFormat::$english;
		$loader = new MemoryDataLoader(CSVDataCODEC::reader(
			$filename, $this->format, $headers,
			$input, $ideal, $significance
		));
		$loader->setResult($this);
		$loader->import();
	}

	public function getFileName(): string {
		return $this->filename;
	}

	public function getFormat(): CSVFormat {
		return $this->format;
	}

	private $filename;
	private $format;
}
