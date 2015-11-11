<?php
/**
 * Copyright 2015-2016 Tim Jurcka <sdgoij@gmail.com>
 *
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
namespace encog\ml\factory\parse;

use encog\EncogError;
use encog\util\SimpleParser;

/**
 * This class is used to parse a Encog architecture string.
 */
final class ArchitectureParser {
	public static function parseLayer(string $line, int $default): ArchitectureLayer {
		$layer = new ArchitectureLayer();
		$check = strtoupper(trim($line));

		if (substr_compare($check, ":B", -2) === 0) {
			$check = substr($check, 0, strlen($check)-2);
			$layer->setBias(true);
		}
		if (($count = (int)$check) < 0) {
			throw new EncogError("Count cannot be less than zero.");
		}
		$layer->setCount($count);

		if ($check == "?") {
			if ($default < 0) {
				throw new EncogError("Default (?) in an invalid location.");
			}
			$layer->setCount($default);
			$layer->setUsedDefault(true);
			return $layer;
		}

		$startIndex = strpos($check, "(");
		$endIndex = strrpos($check, ")");

		if (false === $startIndex) {
			$layer->setName($check);
			return $layer;
		}

		if (false === $endIndex) {
			throw new EncogError("Illegal parentheses.");
		}

		$layer->setParams(self::parseParams(substr($check, $startIndex+1, $endIndex-$startIndex-1)));
		$layer->setName(trim(substr($check, 0, $startIndex)));
		return $layer;
	}

	public static function parseLayers(string $line): array {
		$result = [];
		$base = 0;
		$done = false;
		do {
			if (($index = strpos($line, "->", $base)) !== false) {
				$part = trim(substr($line, $base, $index-$base));
				$base = $index + 2;
			} else {
				$part = trim(substr($line, $base));
				$done = true;
			}
			$result[] = $part;
		} while (!$done);
		return $result;
	}

	public static function parseParams(string $line): array {
		$parser = new SimpleParser($line);
		$result = [];
		while (!$parser->eol()) {
			$name = strtoupper(self::parseName($parser));
			$parser->eatWhiteSpace();
			if (!$parser->lookAhead("=", false)) {
				throw new EncogError("Missing equals(=) operator.");
			}
			$parser->advance();
			$result[$name] = self::parseValue($parser);
			if (!$parser->parseThroughComma()) {
				break;
			}
		}
		return $result;
	}

	private static function parseName(SimpleParser $parser): string {
		$result = "";
		$parser->eatWhiteSpace();
		while ($parser->isIdentifier()) {
			$result .= $parser->readChar();
		}
		return $result;
	}

	private static function parseValue(SimpleParser $parser): string {
		$parser->eatWhiteSpace();
		$quoted = false;
		$result = "";

		if ($parser->peek() == "\"") {
			$quoted = true;
			$parser->advance();
		}

		while (!$parser->eol()) {
			if ($parser->peek() == "\"") {
				if ($quoted) {
					$parser->advance();
					if ($parser->peek() == "\"") {
						$result .= $parser->readChar();
					} else {
						break;
					}
				} else {
					$result .= $parser->readChar();
				}
			} else if (!$quoted && $parser->isWhiteSpace() || $parser->peek() == ",") {
				break;
			} else {
				$result .= $parser->readChar();
			}
		}
		return $result;
	}

	private function __construct() {
	}
}
