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
		if (!isset(self::$registered[$protocol])) {
			self::$registered[$protocol] = stream_wrapper_register($protocol, $class ?? self::class, $flags);
		}
		return self::$registered[$protocol];
	}

	public static function put($path, string $data = "", string $protocol = "memory") {
		$path = parse_url($path, PHP_URL_HOST) ?? $path;
		self::register($protocol, self::class);
		self::$files[$path] = new self($data);
	}

	/** @var MemoryStream[] */
	private static $files = [];
	/** @var bool[] */
	private static $registered = [];

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

	public function stream_seek($offset, $whence) {
		switch ($whence) {
			case SEEK_SET:
				if ($offset <= strlen($this->data) && $offset >= 0) {
					$this->position = $offset;
					return true;
				}
				break;
			case SEEK_CUR:
				if ($offset >= 0) {
					$this->position += $offset;
					return true;
				}
				break;
			case SEEK_END:
				if (strlen($this->data)+$offset >= 0) {
					$this->position = strlen($this->data)+$offset;
					return true;
				}
				break;
		}
		return false;
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

	public function stream_truncate(int $size): bool {
		$this->data = substr($this->data, 0, $size);
		return true;
	}

	public function stream_stat(): array {
		$r = [0, 0, 0, 1, 0, 0, 0, strlen($this->data), time(), time(), time(), -1, -1];
		// uch ... d3rp!
		$r["dev"] = $r[0];
		$r["ino"] = $r[1];
		$r["mode"] = $r[2];
		$r["nlink"] = $r[3];
		$r["uid"] = $r[4];
		$r["gid"] = $r[5];
		$r["rdev"] = $r[6];
		$r["size"] = $r[7];
		$r["atime"] = $r[8];
		$r["mtime"] = $r[9];
		$r["ctime"] = $r[10];
		$r["blksize"] = $r[11];
		$r["blocks"] = $r[12];
		return $r;
	}

	public function url_stat(string $path, int $flags): array {
		$path = parse_url($path, PHP_URL_HOST) ?? $path;
		if (array_key_exists($path, self::$files)) {
			$this->data =& self::$files[$path]->data;
		}
		return $this->stream_stat();
	}

	private $position = 0;
	private $data = "";
}
