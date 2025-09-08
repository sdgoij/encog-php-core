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

use encog\ml\data\MLDataSet;
use encog\ml\MLCluster;
use encog\ml\MLClustering;
use encog\util\kmeans\KMeansUtil;

/**
 * This class performs a basic K-Means clustering. This class can be used on
 * either supervised or unsupervised data. For supervised data, the ideal values
 * will be ignored.
 *
 * http://en.wikipedia.org/wiki/Kmeans
 */
class KMeansClustering implements MLClustering {
	public function __construct(int $k, MLDataSet $dataset) {
		$elements = [];
		foreach ($dataset as $pair) {
			$elements[] = $pair;
		}
		$this->kmeans = new KMeansUtil($k, $elements);
		$this->k = $k;
	}

	public function iteration(int $count = 1) {
		for ($i = 0; $i < $count; $i++) {
			$this->kmeans->process();
			$this->clusters = [];
			for ($j = 0; $j < $this->k; $j++) {
				$this->clusters[] = new BasicCluster(
					$this->kmeans->getCluster($j)
				);
			}
		}
	}

	/** @return MLCluster[] */
	public function getClusters(): array {
		return $this->clusters;
	}

	public function numClusters(): int {
		return $this->k;
	}

	/** @var KMeansUtil */
	private $kmeans;

	/** @var MLCluster[] */
	private $clusters = [];

	/** @var int */
	private $k;
}
