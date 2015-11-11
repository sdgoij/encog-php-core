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
namespace encog\util\kmeans;

/**
 * Generic KMeans clustering object.
 */
class KMeansUtil {
	public function __construct(int $k, array $elements) {
		$this->clusters = [];
		$this->k = $k;

		$this->initRandomClusters($elements);
		assert(count($this->clusters) == $k);
	}

	/** @return CentroidFactory[] */
	public function get(int $index): array {
		return $this->clusters[$index]->getContents();
	}

	public function getCluster(int $index): Cluster {
		return $this->clusters[$index];
	}

	public function size(): int {
		return count($this->clusters);
	}

	public function process() {
		do {
			$done = true;
			for ($i = 0; $i < $this->k; $i++) {
				$cluster = $this->clusters[$i];
				$elements = $cluster->getContents();
				for ($j = 0; $j < count($elements); $j++) {
					if ($cluster->centroid()->distance($elements[$j]) > 0) {
						$nearest = $this->nearestCluster($elements[$j]);
						if ($nearest != $cluster) {
							$nearest->add($elements[$j]);
							$cluster->remove($j);
							$done = false;
						}
					}
				}
			}
		} while (!$done);
	}

	public function nearestCluster($element): Cluster {
		$distance = INF;
		$result = null;
		foreach ($this->clusters as $cluster) {
			$elementDistance = $cluster->centroid()->distance($element);
			if ($distance > $elementDistance) {
				$distance = $elementDistance;
				$result = $cluster;
			}
		}
		assert($result instanceof Cluster);
		return $result;
	}

	private function initRandomClusters(array $elements) {
		$clusterIndex = 0;
		$elementIndex = 0;
		$numElements = count($elements);
		while ($elementIndex < $numElements && $clusterIndex < $this->k &&
				$numElements - $elementIndex > $this->k - $clusterIndex) {
			$element = $elements[$elementIndex];
			$added = false;
			for ($i = 0; $i < $elementIndex; $i++) {
				if (isset($this->clusters[$i]) && $this->clusters[$i]->centroid()->distance($element) == 0.0) {
					$this->clusters[$i]->add($element);
					$added = true;
					break;
				}
			}
			if (!$added) {
				$this->clusters[] = new Cluster($element);
				$clusterIndex++;
			}
			$elementIndex++;
		}
		while ($clusterIndex < $this->k && $elementIndex < $numElements) {
			$this->clusters[] = new Cluster($elements[$elementIndex]);
			$clusterIndex++;
			$elementIndex++;
		}
		while ($clusterIndex < $this->k) {
			$this->clusters[] = new Cluster();
			$clusterIndex++;
		}
		while ($elementIndex < $numElements) {
			$this->nearestCluster($elements[$elementIndex])->add($elements[$elementIndex]);
			$elementIndex++;
		}
	}

	/** @var Cluster[] */
	private $clusters = [];

	/** @var int */
	private $k = 0;
}
