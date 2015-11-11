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
namespace encog\test\ml\kmeans;

use encog\ml\data\basic\BasicMLData;
use encog\ml\data\basic\BasicMLDataSet;
use encog\ml\data\MLDataPair;
use encog\ml\kmeans\KMeansClustering;
use PHPUnit_Framework_TestCase as TestCase;

class KMeansClusteringTest extends TestCase {
	public function testKMeansCluster() {
		$dataset = new BasicMLDataSet();
		foreach (self::DATA as $value) {
			$dataset->add(new BasicMLData($value));
		}
		$kmeans = new KMeansClustering(2, $dataset);
		$kmeans->iteration();

		foreach ($kmeans->getClusters() as $cluster) {
			$records = $cluster->createDataSet();
			$pair = $records->get(0);
			$t = $pair->getInputArray()[0];
			/** @var MLDataPair $pair */
			foreach ($records as $pair) {
				foreach ($pair->getInputArray() as $value) {
					if ($t > 10) {
						$this->assertGreaterThan(10, $value);
					} else {
						$this->assertLessThan(10, $value);
					}
				}
			}
		}
		$this->assertEquals(2, $kmeans->numClusters());
	}

	const DATA = [
		[28, 15, 22],
		[16, 15, 32],
		[32, 20, 44],
		[1, 2, 3],
		[3, 2, 1],
	];
}
