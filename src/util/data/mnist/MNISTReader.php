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
namespace encog\util\data\mnist;

use Countable;
use Iterator;
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
		$this->images = (binary)file_get_contents("compress.zlib://$images");
		$this->labels = (binary)file_get_contents("compress.zlib://$labels");
		$this->index = 0;

		$imageFileHeaders = $this->headers(substr($this->images, 0, 16));
		$labelFileHeaders = $this->headers(substr($this->labels, 0, 8));

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

	public function current(): array {
		if (!$this->current) {
			$imageData = substr($this->images, 16+$this->imageRecordSize*$this->index, $this->imageRecordSize);
			$this->current = [
				array_values(unpack("C{$this->imageRecordSize}", $imageData)),
				unpack("C", substr($this->labels, 8+1*$this->index, 1))[1],
			];
		}
		return $this->current;
	}

	public function next(): bool {
		$this->current = null;
		return $this->index++ < $this->size;
	}

	public function seek(int $index) {
		if ($index < 0 || $index >= $this->size) {
			throw new MNISTError("Index is out of bounds.");
		}
		$this->current = null;
		$this->index = $index;
	}

	public function getImageRecordSize(): int {
		return $this->imageRecordSize;
	}

	public function getIterator(): Iterator {
		do {
			yield $this->current();
		} while ($this->next());
		$this->index = 0;
	}

	public function count(): int {
		return $this->size;
	}

	private function headers($data): array {
		return array_values(unpack("N".strlen($data)/4, $data));
	}

	private $current;
	private $index;
	private $imageRecordSize;
	private $labels;
	private $images;
	private $size;
}
