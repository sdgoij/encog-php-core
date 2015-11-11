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
namespace encog\neural\pattern;

use encog\engine\network\activation\ActivationFunction;
use encog\engine\network\activation\ActivationLinear;
use encog\ml\MLMethod;
use encog\neural\networks\BasicNetwork;
use encog\neural\networks\layers\BasicLayer;

/**
 * Used to create feedforward neural networks. A feedforward network has an
 * input and output layers separated by zero or more hidden layers. The
 * feedforward neural network is one of the most common neural network patterns.
 */
class FeedForwardPattern implements NetworkPattern {

	/** @var int */
	private $inputNeurons;

	/** @var int */
	private $outputNeurons;

	/** @var ActivationFunction */
	private $hiddenActivation;

	/** @var ActivationFunction */
	private $outputActivation;

	/** @var int[] */
	private $hidden = [];

	public function addHiddenLayer(int $neurons) {
		$this->hidden[] = $neurons;
	}

	public function clear() {
		$this->hidden = [];
	}

	public function generate(): MLMethod {
		if (!$this->outputActivation) {
			$this->outputActivation = $this->hiddenActivation;
		}
		$network = new BasicNetwork();
		$network->addLayer(BasicLayer::create($this->inputNeurons, new ActivationLinear()));
		foreach ($this->hidden as $neurons) {
			$network->addLayer(BasicLayer::create($neurons, $this->hiddenActivation));
		}
		$network->addLayer(BasicLayer::create($this->outputNeurons,
			$this->outputActivation, false));
		$network->getStructure()->finalizeStructure();
		$network->reset();
		return $network;
	}

	public function setActivationFunction(ActivationFunction $activation) {
		$this->hiddenActivation = $activation;
	}

	public function setOutputActivation(ActivationFunction $activation) {
		$this->outputActivation = $activation;
	}

	public function setOutputNeurons(int $count) {
		$this->outputNeurons = $count;
	}

	public function setInputNeurons(int $count) {
		$this->inputNeurons = $count;
	}
}
