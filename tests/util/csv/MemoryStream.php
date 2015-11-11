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
namespace encog\test\util\csv;

final class MemoryStream {
	public static function register(string $protocol, string $class = null, int $flags = 0): bool {
		if  (!self::$registered) {
			self::$registered = stream_wrapper_register($protocol, $class ?? self::class, $flags);
		}
		return self::$registered;
	}

	public static function put($path, string $data = "") {
		$path = parse_url($path, PHP_URL_HOST) ?? $path;
		self::register("memory", self::class);
		self::$files[$path] = new self($data);
	}

	/** @var MemoryStream[] */
	private static $files = [];
	private static $registered = false;

	public function __construct(string $data = "") {
		$this->data = $data;
	}

	public function stream_open(string $path, string $mode, int $options, &$opened): bool {
		$path = parse_url($path, PHP_URL_HOST) ?? $path;
		if (array_key_exists($path, self::$files)) {
			$this->data =& self::$files[$path]->data;
		} else {
			self::$files[$path] = $this;
		}
		$opened = $path;
		return true;
	}

	public function stream_read(int $count): string {
		$data = substr($this->data, $this->position, $count);
		$this->position += $count;
		return $data;
	}

	function stream_seek($offset, $whence) {
		switch ($whence) {
			case SEEK_SET:
				if ($offset < strlen($this->data) && $offset >= 0) {
					$this->position = $offset;
					return true;
				}
				return false;
			case SEEK_CUR:
				if ($offset >= 0) {
					$this->position += $offset;
					return true;
				}
				return false;
			case SEEK_END:
				if (strlen($this->data)+$offset >= 0) {
					$this->position = strlen($this->data)+$offset;
					return true;
				}
				return false;
			default:
				return false;
		}
	}

	public function stream_write(string $data): int {
		$length = strlen($data);
		$left = substr($this->data, 0, $this->position);
		$right = substr($this->data, $this->position+$length) ?? "";
		$this->data = $left . $data . $right;
		$this->position += $length;
		return $length;
	}

	public function stream_tell(): int {
		return $this->position;
	}

	public function stream_eof(): bool {
		return $this->position >= strlen($this->data);
	}

	private $position = 0;
	private $data = "";
}
