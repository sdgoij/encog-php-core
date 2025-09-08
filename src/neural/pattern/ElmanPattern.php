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
namespace encog\neural\pattern;

use encog\ml\MLMethod;
use encog\neural\networks\BasicNetwork;
use encog\neural\networks\layers\BasicLayer;

/**
 * This class is used to generate an Elman style recurrent neural network. This
 * network type consists of three regular layers, an input output and hidden
 * layer. There is also a context layer which accepts output from the hidden
 * layer and outputs back to the hidden layer. This makes it a recurrent neural
 * network.
 *
 * The Elman neural network is useful for temporal input data. The specified
 * activation function will be used on all layers. The Elman neural network is
 * similar to the Jordan neural network.
 */
class ElmanPattern extends AbstractRecurrentPattern {
	public function addHiddenLayer(int $neurons) {
		if ($this->hidden != -1) {
			throw new PatternError("A Elman neural network should have only one hidden layer.");
		}
		$this->hidden = $neurons;
	}

	public function generate(): MLMethod {
		$input = BasicLayer::create($this->input);
		$hidden = BasicLayer::create($this->hidden, $this->activation);
		$input->setContextFedBy($hidden);
		$network = new BasicNetwork();
		$network->addLayer($input);
		$network->addLayer($hidden);
		$network->addLayer(BasicLayer::create($this->output, null, false));
		$network->getStructure()->finalizeStructure();
		$network->reset();
		return $network;
	}
}
