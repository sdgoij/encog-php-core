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
namespace encog\ml\factory;

use encog\Encog;
use encog\EncogError;
use encog\ml\MLMethod;
use encog\plugin\EncogPluginError;
use encog\plugin\EncogPluginService1;

class MLMethodFactory {

	const TYPE_BAYESIAN    = "bayesian";
	const TYPE_EPL         = "epl";
	const TYPE_FEEDFORWARD = "feedforward";
	const TYPE_NEAT        = "neat";
	const TYPE_PNN         = "pnn";
	const TYPE_RBFNETWORK  = "rbfnetwork";
	const TYPE_SOM         = "som";
	const TYPE_SVM         = "svm";

	const PROPERTY_POPULATION_SIZE = "population";
	const PROPERTY_CYCLES          = "cycles";
	const PROPERTY_AF              = "AF";

	public function create(string $methodType, string $arch, int $input, int $output): MLMethod {
		foreach (Encog::getInstance()->getPlugins() as $plugin) {
			if ($plugin instanceof EncogPluginService1) {
				try {
					return $plugin->createMethod($methodType, $arch, $input, $output);
				} catch (EncogPluginError $e) {}
			}
		}
		throw new EncogError("Unknown method type: $methodType");
	}
}
