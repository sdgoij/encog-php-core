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
namespace encog\util\csv;

use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use InvalidArgumentException;

use encog\EncogError;

/**
 * Read and parse CSV format files.
 */
class CSVReader {
	private $format;
	private $reader;
	private $columns = [];
	private $data = [];
	private $columnNames = [];
	private $lineParser;

	public static function displayDate(DateTimeInterface $date): string {
		return $date->format("Ymd");
	}

	public static function parseDate(string $str): DateTimeInterface {
		return new DateTimeImmutable($str);
	}

	public static function createFromFileName(string $filename, bool $headers, $format): CSVReader {
		return new static(@fopen($filename, 'r'), $headers, $format);
	}

	public function __construct($reader, bool $headers, $format) {
		if (!$reader || !is_resource($reader)) {
			throw new InvalidArgumentException("\$reader is not a valid stream resource.");
		}
		if (!$format instanceof CSVFormat) {
			$format = new CSVFormat(CSVFormat::getDecimalCharacter(), $format);
		}
		$this->lineParser = new CSVLineParser($format);
		$this->format = $format;
		$this->reader = $reader;
		$this->begin($headers);
	}

	public function getColumnNames(): array {
		return $this->columnNames;
	}

	public function getFormat(): CSVFormat {
		return $this->format;
	}

	public function hasMissing(): bool {
		foreach ($this->data as $v) {
			$v = trim($v);
			if (!strlen($v) || $v == "?") {
				return true;
			}
		}
		return false;
	}

	private function begin(bool $headers) {
		if ($headers) {
			$this->columnNames = [];
			if (false !== $line = fgets($this->reader)) {
				foreach ($this->lineParser->parse($line) as $k => $header) {
					$header = strtolower($header);
					$this->columnNames[] = $header;
					$this->columns[$header] = $k;
				}
			}
		}
		$this->data = [];
	}

	public function close() {
		fclose($this->reader);
	}

	public function get($index): string {
		if (is_string($index)) {
			$index = strtolower($index);
			if (!array_key_exists($index, $this->columns)) {
				return "";
			}
			$index = $this->columns[$index];
		}
		if ($index >= count($this->data)) {
			throw new EncogError("Can't access column [$index] in a file that has only " . count($this->data) . " columns.");
		}
		return $this->data[$index];
	}

	public function getColumnCount(): int {
		return count($this->data);
	}

	public function getDate($column): DateTimeInterface {
		try {
			return static::parseDate($this->get($column));
		} catch (Exception $e) {
			throw new EncogError($e->getMessage(), $e->getCode(), $e);
		}
	}

	public function getDouble($column): float {
		return $this->format->parse($this->get($column));
	}

	public function getInt($column): int {
		return $this->format->getNumberFormatter()
			->parse($this->get($column))->intValue();
	}

	public function next(): bool {
		do {
			$line = fgets($this->reader);
			if ($line === false) {
				return false;
			}
		} while (!feof($this->reader) && !strlen(trim($line)));

		$this->data = $this->lineParser->parse($line);
		return true;
	}
}
