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
namespace encog\util\kmeans;

/**
 * A cluster.
 */
class Cluster {
	public function __construct(CentroidFactory $data = null) {
		if ($data !== null) {
			$this->centroid = $data->createCentroid();
			$this->contents[] = $data;
		}
	}

	public function add(CentroidFactory $element) {
		if ($this->centroid === null) {
			$this->centroid = $element->createCentroid();
		} else {
			$this->centroid->add($element);
		}
		$this->contents[] = $element;
	}

	public function remove(int $index) {
		if (isset($this->contents[$index])) {
			$this->centroid->remove($this->contents[$index]);
			array_splice($this->contents, $index, 1);
		}
	}

	/** @return CentroidFactory[] */
	public function getContents(): array {
		return $this->contents;
	}

	public function centroid(): Centroid {
		if (!$this->centroid) {
			return new class implements Centroid {
				public function add($element) {}
				public function distance($element): float { return 0.0; }
				public function remove($element) {}
			};
		}
		return $this->centroid;
	}

	/** @var CentroidFactory[] */
	private $contents = [];
	/** @var Centroid */
	private $centroid;
}
