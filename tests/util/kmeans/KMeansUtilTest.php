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
namespace encog\test\util\kmeans;

use encog\ml\data\basic\BasicMLData;
use encog\ml\data\basic\BasicMLDataPair;
use encog\util\kmeans\KMeansUtil;
use PHPUnit\Framework\TestCase;

class KMeansUtilTest extends TestCase {
	public function testInitClusters() {
		$kmeans = new KMeansUtil(2, [
			new BasicMLDataPair(new BasicMLData([0,0]), new BasicMLData([0])),
			new BasicMLDataPair(new BasicMLData([0,1]), new BasicMLData([1])),
			new BasicMLDataPair(new BasicMLData([1,0]), new BasicMLData([1])),
			new BasicMLDataPair(new BasicMLData([1,1]), new BasicMLData([0])),
		]);
		$this->assertEquals(2, count($kmeans->getCluster(0)->getContents()));
		$this->assertEquals(2, count($kmeans->getCluster(1)->getContents()));
		$this->assertEquals(2, $kmeans->size());

		$kmeans = new KMeansUtil(4, [
			new BasicMLDataPair(new BasicMLData([0,0]), new BasicMLData([0])),
		]);
		$this->assertEquals(1, count($kmeans->getCluster(0)->getContents()));
		$this->assertEquals(0, count($kmeans->getCluster(1)->getContents()));
		$this->assertEquals(0, count($kmeans->getCluster(2)->getContents()));
		$this->assertEquals(0, count($kmeans->getCluster(3)->getContents()));
		$this->assertEquals(4, $kmeans->size());

		$kmeans = new KMeansUtil(2, [
			new BasicMLDataPair(new BasicMLData([0,0]), new BasicMLData([0])),
			new BasicMLDataPair(new BasicMLData([0,0]), new BasicMLData([0])),
			new BasicMLDataPair(new BasicMLData([0,0]), new BasicMLData([0])),
		]);

		$this->assertEquals(2, count($kmeans->getCluster(0)->getContents()));
		$this->assertEquals(1, count($kmeans->getCluster(1)->getContents()));
		$this->assertEquals(2, $kmeans->size());
	}

	public function testProcessKMeans() {
		$pairs = [
			new BasicMLDataPair(new BasicMLData([28, 15, 22])),
			new BasicMLDataPair(new BasicMLData([16, 15, 32])),
			new BasicMLDataPair(new BasicMLData([32, 20, 44])),
			new BasicMLDataPair(new BasicMLData([1, 2, 3])),
			new BasicMLDataPair(new BasicMLData([3, 2, 1])),
		];
		$kmeans = new KMeansUtil(2, $pairs);
		$this->assertEquals(3, count($kmeans->get(0)));
		$this->assertEquals(2, count($kmeans->get(1)));
		$kmeans->process();

		$this->assertEquals(2, count($kmeans->get(0)));
		$this->assertEquals(3, count($kmeans->get(1)));
		$this->assertEquals(2, $kmeans->size());
	}
}
