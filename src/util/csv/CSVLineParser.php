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

use encog\util\SimpleParser;

class CSVLineParser {
	public function __construct(CSVFormat $format) {
		$this->format = $format;
	}

	public function parse(string $line): array {
		return $this->format->getSeparator() == " "
			? $this->parseSpaceSep($line)
			: $this->parseCharSep($line);
	}

	private function parseSpaceSep(string $line): array {
		$parser = new SimpleParser($line);
		$result = [];
		while (!$parser->eol()) {
			if ($parser->peek() == "\"") {
				$result[] = $parser->readQuotedString();
			} else {
				$result[] = $parser->readToWhiteSpace();
			}
			$parser->eatWhiteSpace();
		}
		return $result;
	}

	private function parseCharSep(string $line): array {
		$result = [];
		$item = "";
		$hadQuotes = false;
		$quoted = false;

		for ($i = 0; $i < strlen($line); $i++) {
			if ($line[$i] == $this->format->getSeparator() && !$quoted) {
				if (!$hadQuotes) {
					$item = trim($item);
				}
				$result[] = $item;
				$item = "";
				$hadQuotes = false;
				$quoted = false;
			} else if ($line[$i] == "\"" && $quoted) {
				if ($i+1 < strlen($line) && $line[$i+1] == "\"") {
					$item .= "\"";
					$i++;
				} else {
					$quoted = false;
				}
			} else if ($line[$i] == "\"" && !strlen($item)) {
				$hadQuotes = true;
				$quoted = true;
			} else {
				$item .= $line[$i];
			}
		}
		if (strlen($item) > 0) {
			if (!$hadQuotes) {
				$item = trim($item);
			}
			$result[] = $item;
		}
		return $result;
	}

	/** @var CSVFormat */
	private $format;
}
