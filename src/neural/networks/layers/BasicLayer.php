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
namespace encog\neural\networks\layers;

use encog\engine\network\activation\ActivationFunction;
use encog\engine\network\activation\ActivationSigmoid;
use encog\neural\flat\FlatLayer;
use encog\neural\networks\BasicNetwork;

/**
 * Basic functionality that most of the neural layers require. The basic layer
 * is often used by itself to implement forward or recurrent layers. Other layer
 * types are based on the basic layer as well.
 *
 * The following summarizes how basic layers calculate the output for a neural
 * network.
 *
 * Example of a simple XOR network.
 *
 * Input: BasicLayer: 2 Neurons, null biasWeights, null biasActivation
 *
 * Hidden: BasicLayer: 2 Neurons, 2 biasWeights, 1 biasActivation
 *
 * Output: BasicLayer: 1 Neuron, 1 biasWeights, 1 biasActivation
 *
 * Input1Output and Input2Output are both provided.
 *
 * Synapse 1: Input to Hidden Hidden1Activation =
 * (Input1Output * Input1→Hidden1Weight) +
 * (Input2Output * Input2→Hidden1Weight) +
 * (HiddenBiasActivation * Hidden1BiasWeight)
 *
 * Hidden1Output = calculate(Hidden1Activation, HiddenActivationFunction)
 *
 * Hidden2Activation = (Input1Output * Input1→Hidden2Weight)
 * + (Input2Output * Input2→Hidden2Weight)
 * + (HiddenBiasActivation * Hidden2BiasWeight)
 *
 * Hidden2Output = calculate(Hidden2Activation, HiddenActivationFunction)
 *
 * Synapse 2: Hidden to Output
 *
 * Output1Activation = (Hidden1Output * Hidden1→Output1Weight)
 * + (Hidden2Output * Hidden2→Output1Weight)
 * + (OutputBiasActivation * Output1BiasWeight)
 *
 * Output1Output = calculate(Output1Activation, OutputActivationFunction)
 */
class BasicLayer extends FlatLayer implements Layer {
	public static function create(int $neuronCount, ?ActivationFunction $af = null, bool $hasBias = true): BasicLayer {
		return new static($af ?? new ActivationSigmoid(), $neuronCount, $hasBias ? 1.0 : 0.0);
	}

	public function getActivationFunction(): ActivationFunction {
		return $this->getActivation();
	}

	public function getNetwork(): ?BasicNetwork {
		return $this->network;
	}

	public function setNetwork(BasicNetwork $network) {
		$this->network = $network;
	}

	public function getNeuronCount(): int {
		return $this->getCount();
	}

	/** @var BasicNetwork */
	private $network;
}
