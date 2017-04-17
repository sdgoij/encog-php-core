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
namespace encog\test\neural\networks;

use encog\ml\data\basic\BasicMLData;
use encog\ml\data\basic\BasicMLDataSet;
use encog\ml\data\MLDataSet;
use encog\ml\MLRegression;
use encog\neural\networks\BasicNetwork;
use encog\neural\networks\layers\BasicLayer;
use encog\neural\networks\structure\NetworkCODEC;
use encog\util\simple\EncogUtility;

class XORUtil {
	const INPUT = [[0.0,0.0],[1.0,0.0],[0.0,1.0],[1.0,1.0]];
	const IDEAL = [[0.0],[1.0],[1.0],[0.0]];

	public static function verify(MLRegression $network, float $tolerance): bool {
		foreach (self::IDEAL as $key => $values) {
			$actual = $network->compute(new BasicMLData(self::INPUT[$key]));
			foreach ($values as $index => $value) {
				if (abs($actual->getDataAt($index) - $value) > $tolerance) {
					return false;
				}
			}
		}
		return true;
	}

	public static function createDataSet(): MLDataSet {
		return new BasicMLDataSet(self::INPUT, self::IDEAL);
	}

	public static function createTrainedNetwork(): BasicNetwork {
		$weights = [25.427193285452972,-26.92000502099534,20.76598054603445,-12.921266548020219,-0.9223427050161919,-1.0588373209475093,-3.80109620509867,3.1764938777876837,80.98981535707951,-75.5552829139118,37.089976176012634,74.85166823997326,75.20561368661059,-37.18307123471437,-21.044949631177417,43.81815044327334,9.648991753485689];
		$network = EncogUtility::simpleFeedForward(2, 4, 0, 1, false);
		NetworkCODEC::arrayToNetwork($weights, $network);
		return $network;
	}

	public static function createUnTrainedNetwork(): BasicNetwork {
		$weights = [-0.427193285452972,0.92000502099534,-0.76598054603445,-0.921266548020219,-0.9223427050161919,-0.0588373209475093,-0.80109620509867,3.1764938777876837,0.98981535707951,-0.5552829139118,0.089976176012634,0.85166823997326,0.20561368661059,0.18307123471437,0.044949631177417,0.81815044327334,0.648991753485689];
		$network = EncogUtility::simpleFeedForward(2, 4, 0, 1, false);
		NetworkCODEC::arrayToNetwork($weights, $network);
		return $network;
	}

	public static function createThreeLayerNetwork(): BasicNetwork {
		$network = new BasicNetwork();
		$network->addLayer(BasicLayer::create(2));
		$network->addLayer(BasicLayer::create(3));
		$network->addLayer(BasicLayer::create(1));
		$network->getStructure()->finalizeStructure();
		$network->reset();
		return $network;
	}
}
