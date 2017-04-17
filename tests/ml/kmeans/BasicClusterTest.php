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
namespace encog\test\ml\kmeans;

use encog\ml\data\basic\BasicMLData;
use encog\ml\data\basic\BasicMLDataPair;
use encog\ml\data\basic\BasicMLDataPairCentroid;
use encog\ml\data\basic\BasicMLDataSet;
use encog\ml\kmeans\BasicCluster;
use encog\util\kmeans\Centroid;
use encog\util\kmeans\Cluster;
use PHPUnit_Framework_TestCase as TestCase;

class BasicClusterTest extends TestCase {
	public function testCreateCluster() {
		$cluster = $this->createBasicCluster();
		$this->assertInstanceOf(BasicMLDataPairCentroid::class, $cluster->getCentroid());
		$this->assertEquals(1, $cluster->size());
	}

	public function testAddElement() {
		$cluster = $this->createBasicCluster();
		$cluster->add(new BasicMLData(KMeansClusteringTest::DATA[1]));
		$this->assertEquals(2, $cluster->size());
		$this->assertEquals(
			[
				new BasicMLData(KMeansClusteringTest::DATA[0]),
				new BasicMLData(KMeansClusteringTest::DATA[1]),
			],
			$cluster->getData()
		);
	}

	public function testRemoveElement() {
		$cluster = $this->createBasicCluster();
		$cluster->remove($cluster->get(0));
		$this->assertEquals(0, $cluster->size());
	}

	public function testCreateDataSet() {
		$expected = new BasicMLDataSet([new BasicMLDataPair(new BasicMLData(KMeansClusteringTest::DATA[0]))]);
		foreach ($this->createBasicCluster()->createDataSet() as $key => $value) {
			$this->assertEquals($expected->get($key), $value);
		}
	}

	public function testSetCentroid() {
		$cluster = $this->createBasicCluster();
		$cluster->setCentroid(new class implements Centroid {
			public function add($element) {}
			public function distance($element): float { return 4.2; }
			public function remove($element) {}
		});
		$this->assertEquals(4.2, $cluster->getCentroid()->distance(1));
	}

	private function createBasicCluster(): BasicCluster {
		return new BasicCluster(new Cluster(new BasicMLDataPair(
			new BasicMLData(KMeansClusteringTest::DATA[0])
		)));
	}
}
