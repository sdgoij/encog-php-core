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
namespace encog\ml\kmeans;

use encog\ml\data\basic\BasicMLDataPair;
use encog\ml\data\basic\BasicMLDataPairCentroid;
use encog\ml\data\basic\BasicMLDataSet;
use encog\ml\data\MLData;
use encog\ml\data\MLDataSet;
use encog\ml\MLCluster;
use encog\util\kmeans\Centroid;
use encog\util\kmeans\Cluster;

/**
 * Holds a cluster of MLData items that have been clustered
 * by the KMeansClustering class.
 */
class BasicCluster implements MLCluster {
	public function __construct(Cluster $cluster) {
		$this->centroid = $cluster->centroid();
		/** @var BasicMLDataPair $pair */
		foreach ($cluster->getContents() as $pair) {
			$this->data[] = $pair->getInput();
		}
	}

	public function add(MLData $data) {
		$this->data[] = $data;
	}

	public function createDataSet(): MLDataSet {
		$dataset = new BasicMLDataSet();
		foreach ($this->data as $value) {
			$dataset->add($value);
		}
		return $dataset;
	}

	public function get(int $pos): MLData {
		return $this->data[$pos];
	}

	/** @return MLData[] */
	public function getData(): array {
		return $this->data;
	}

	public function remove(MLData $data) {
		if (false !== $index = array_search($data, $this->data, true)) {
			unset($this->data[$index]);
		}
	}

	public function size(): int {
		return count($this->data);
	}

	public function getCentroid(): Centroid {
		return $this->centroid;
	}

	public function setCentroid(Centroid $centroid) {
		$this->centroid = $centroid;
	}

	/** @var Centroid */
	private $centroid;

	/** @var MLData[] */
	private $data;
}
