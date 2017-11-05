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
use encog\engine\network\activation\ActivationFunction;
use encog\plugin\EncogPluginError;
use encog\plugin\EncogPluginService1;
use encog\util\logging\EncogLogging;

class MLActivationFactory {

	const BIPOLAR     = "bipolar";
	const COMPETITIVE = "comp";
	const ELLIOTT     = "elliott";
	const ELLIOTTSYM  = "elliottsymmetric";
	const GAUSSIAN    = "gauss";
	const LINEAR      = "linear";
	const LOG         = "log";
	const RAMP        = "ramp";
	const RELU        = "relu";
	const SIGMOID     = "sigmoid";
	const SIN         = "sin";
	const SOFTMAX     = "softmax";
	const SSIGMOID    = "ssigmoid";
	const STEP        = "step";
	const TANH        = "tanh";

	public function create(string $name): ?ActivationFunction {
		foreach (Encog::getInstance()->getPlugins() as $plugin) {
			if ($plugin instanceof EncogPluginService1) {
				try {
					return $plugin->createActivationFunction($name);
				} catch (EncogPluginError $e) {}
			}
		}
		EncogLogging::log(EncogLogging::LEVEL_INFO, "Unknown activation function: $name");
		return null;
	}
}
