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
use encog\util\kmeans\Cluster;
use PHPUnit\Framework\TestCase;

class ClusterTest extends TestCase {
	public function testEmptyCluster() {
		$cluster = new Cluster();
		$this->assertEquals(0.0, $cluster->centroid()->distance("1"));
		$this->assertEquals([], $cluster->getContents());
		$cluster->remove(0);
	}

	public function testCentroidDistance() {
		$pair1 = BasicMLDataPair::createPair(2, 1);
		$pair1->setInputArray([0, 1]);
		$pair1->setIdealArray([1]);

		$pair2 = BasicMLDataPair::createPair(2, 1);
		$pair2->setInputArray([1, 1]);
		$pair2->setIdealArray([0]);

		$cluster = new Cluster($pair1);

		$this->assertEquals([$pair1], $cluster->getContents());
		$this->assertEquals(0.0, $cluster->centroid()->distance($pair1));
		$this->assertEquals(1.0, $cluster->centroid()->distance($pair2));
	}

	public function testAddElement() {
		$cluster = new Cluster();
		$pair = new BasicMLDataPair(new BasicMLData([1,0]), new BasicMLData([1]));

		$this->assertEquals([], $cluster->getContents());

		$cluster->add($pair);
		$cluster->add($pair);

		$this->assertEquals([$pair, $pair], $cluster->getContents());
	}

	public function testRemoveElement() {
		$cluster = new Cluster(new BasicMLDataPair(new BasicMLData([1,0]), new BasicMLData([1])));
		$cluster->add($cluster->getContents()[0]);
		$cluster->add($cluster->getContents()[0]);

		$this->assertEquals(3, count($cluster->getContents()));
		$this->assertEquals(0.0, $cluster->centroid()->distance($cluster->getContents()[0]));

		while ($elements = count($cluster->getContents())) {
			$cluster->remove($elements-1);
			$this->assertEquals($elements-1, count($cluster->getContents()));
		}
		$this->assertEquals(0, count($cluster->getContents()));
	}
}
