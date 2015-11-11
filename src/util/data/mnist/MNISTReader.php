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
namespace encog\util\data\mnist;

use Countable;
use IteratorAggregate;

/**
 * MNISTReader reads the MNIST dataset of handwritten digits labels and images.
 * The MNIST dataset is found at http://yann.lecun.com/exdb/mnist/.
 */
class MNISTReader implements Countable, IteratorAggregate {
	public function __construct(string $labels, string $images, int $start = 0, int $limit = -1) {
		if (!file_exists($labels)) {
			throw new MNISTError("File '$labels' not found!");
		}
		if (!file_exists($images)) {
			throw new MNISTError("File '$images' not found!");
		}
		$this->images = fopen($images, "rb");
		$this->labels = fopen($labels, "rb");

		$imageFileHeaders = $this->headers($this->images, 16);
		$labelFileHeaders = $this->headers($this->labels, 8);

		$this->imageFileName = $images;
		$this->labelFileName = $labels;

		if (2051 != $imageFileHeaders[0]) {
			throw new MNISTError("Image file has wrong magic number: {$imageFileHeaders[0]} (should be 2051)");
		}
		if (2049 != $labelFileHeaders[0]) {
			throw new MNISTError("Label file has wrong magic number: {$labelFileHeaders[0]} (should be 2049)");
		}

		if ($imageFileHeaders[1] != $labelFileHeaders[1]) {
			$message  = "Image file and label file do not contain the same number of entries.\n";
			$message .= "  Label file contains: {$labelFileHeaders[1]}\n";
			$message .= "  Image file contains: {$imageFileHeaders[1]}\n";
			throw new MNISTError($message);
		}

		$this->imageRecordSize = $imageFileHeaders[2] * $imageFileHeaders[3];
		$this->size = $imageFileHeaders[1];

		if ($limit > -1) {
			$this->size = min($limit, $this->size);
		}
		if ($start > 0) {
			$this->seek($start);
		}
	}

	public function __destruct() {
		$this->close();
	}

	public function __clone() {
		$this->images = fopen($this->imageFileName, "rb");
		$this->labels = fopen($this->labelFileName, "rb");
		$this->current = null;
	}

	public function close() {
		if ($this->images) {
			fclose($this->images);
			$this->images = null;
		}
		if ($this->labels) {
			fclose($this->labels);
			$this->labels = null;
		}
	}

	public function current(): array {
		if (!$this->current) {
			$this->current = $this->next();
		}
		return $this->current;
	}

	public function next(): array {
		if (!feof($this->images) && $rawLabelData = fread($this->labels, 1)) {
			return [
				array_values(unpack("C{$this->imageRecordSize}", fread($this->images, $this->imageRecordSize))),
				unpack("C", $rawLabelData)[1],
			];
		}
		return [];
	}

	public function seek(int $index) {
		if ($index < 0 || $index >= $this->size) {
			throw new MNISTError("Index is out of bounds.");
		}
		fseek($this->images, 16+$index*$this->imageRecordSize);
		fseek($this->labels, 8+$index);
		$this->current = null;
	}

	public function getImageRecordSize(): int {
		return $this->imageRecordSize;
	}

	public function getIterator() {
		while ($data = $this->next()) {
			yield $data;
		}
	}

	public function count() {
		return $this->size;
	}

	private function headers($stream, int $size): array {
		return array_values(unpack("N".$size/4, fread($stream, $size)));
	}

	private $imageFileName;
	private $labelFileName;
	private $current;
	private $imageRecordSize;
	private $labels;
	private $images;
	private $size;
}
