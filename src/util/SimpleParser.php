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
namespace encog\util;

class SimpleParser {
	/** @var string */
	private $line;

	/** @var int */
	private $position;

	/** @var int */
	private $marked;

	public function __construct(string $line) {
		$this->line = $line;
		$this->position = 0;
		$this->marked = 0;
	}

	public function __toString() {
		return sprintf("[Parser:%s]", substr($this->line, $this->position));
	}

	public function getLine(): string {
		return $this->line;
	}

	public function remaining(): int {
		return max(strlen($this->line) - $this->position, 0);
	}

	public function parseThroughComma(): bool {
		$this->eatWhiteSpace();
		if (!$this->eol() && $this->peek() == ",") {
			$this->advance();
			return true;
		}
		return false;
	}

	public function isIdentifier(): bool {
		if (!$this->eol()) {
			return ctype_alnum($this->peek()) || $this->peek() == "_";
		}
		return false;
	}

	public function peek(): string {
		if (!$this->eol()) {
			return $this->line[$this->position];
		}
		return "";
	}

	public function advance() {
		if ($this->position < strlen($this->line)) {
			$this->position++;
		}
	}

	public function advanceTo(int $pos) {
		$this->position = min(strlen($this->line), $this->position+$pos);
	}

	public function isWhiteSpace(): bool {
		return ctype_space($this->peek());
	}

	public function eol(): bool {
		return $this->position >= strlen($this->line);
	}

	public function eatWhiteSpace() {
		while (!$this->eol() && $this->isWhiteSpace()) $this->advance();
	}

	public function readChar(): string {
		$char = "";
		if (!$this->eol()) {
			$char = $this->peek();
			$this->advance();
		}
		return $char;
	}

	public function readToWhiteSpace(): string {
		$result = "";
		while (!$this->eol() && !$this->isWhiteSpace()) {
			$result .= $this->readChar();
		}
		return $result;
	}

	public function readQuotedString(): string {
		$result = "";
		if ($this->peek() == "\"") {
			$this->advance();
			while (!$this->eol() && $this->peek() != "\"") {
				$result .= $this->readChar();
			}
			$this->advance();
		}
		return $result;
	}

	public function readToChars(string $chars): string {
		$result = "";
		while (!$this->eol() && strpos($chars, $this->peek()) === false) {
			$result .= $this->readChar();
		}
		return $result;
	}

	public function lookAhead(string $str, bool $ignoreCase = false): bool {
		if ($this->remaining() < strlen($str)) {
			return false;
		}
		for ($i = 0; $i < strlen($str); $i++) {
			$c1 = $this->line[$this->position+$i];
			$c2 = $str[$i];

			if ($ignoreCase) {
				$c1 = strtolower($c1);
				$c2 = strtolower($c2);
			}
			if ($c1 != $c2) {
				return false;
			}
		}
		return true;
	}

	public function skip(string $chars) {
		$this->advanceTo($this->position+strlen($chars));
	}

	public function mark() {
		$this->marked = $this->position;
	}

	public function reset() {
		$this->position = $this->marked;
	}
}
