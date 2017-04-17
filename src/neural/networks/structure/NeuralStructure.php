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
namespace encog\neural\networks\structure;

use encog\neural\flat\FlatLayer;
use encog\neural\flat\FlatNetwork;
use encog\neural\networks\BasicNetwork;
use encog\neural\networks\layers\Layer;
use encog\neural\NeuralNetworkError;

/**
 * Holds "cached" information about the structure of the neural network. This is
 * a very good performance boost since the neural network does not need to
 * traverse itself each time a complete collection of layers or synapses is
 * needed.
 */
class NeuralStructure {
	/** @var Layer[] */
	private $layers = [];

	/** @var BasicNetwork */
	private $network;

	/** @var FlatNetwork */
	private $flat;

	/** @var float */
	private $connectionLimit;

	/** @var bool */
	private $connectionLimited;

	public function __construct(BasicNetwork $network) {
		$this->network = $network;
	}

	public final function calculateSize(): int {
		return NetworkCODEC::networkSize($this->network);
	}

	public final function enforceLimit() {
		if ($this->connectionLimited) {
			$weights = $this->flat->getWeights();
			foreach ($weights as $key => $weight) {
				if (abs($weight) < $this->connectionLimit) {
					$weights[$key] = 0;
				}
			}
			$this->flat->setWeights($weights);
		}
	}

	public function finalizeLimit() {
		$limit = $this->network->getPropertyString(BasicNetwork::TAG_LIMIT);
		if ($limit !== null) {
			$this->connectionLimited = true;
			$this->connectionLimit = (float)$limit;
			$this->enforceLimit();
		} else {
			$this->connectionLimited = false;
			$this->connectionLimit = 0.0;
		}
	}

	public function finalizeStructure(bool $dropout = false) {
		if (count($this->layers) < 2) {
			throw new NeuralNetworkError("There must be at least two layers before the structure is finalized.");
		}
		foreach ($this->layers as $layer) {
			if (!$layer instanceof FlatLayer) {
				throw new NeuralNetworkError("Unsupported Layer type.");
			}
		}
		$this->flat = FlatNetwork::createFromArray($this->layers, $dropout);
		$this->finalizeLimit();
		$this->layers = [];
	}

	public function isConnectionLimited(): bool {
		return $this->connectionLimited;
	}

	public function getConnectionLimit(): float {
		return $this->connectionLimit;
	}

	public function getFlat() {
		return $this->flat;
	}

	public function addLayer(Layer $layer) {
		$this->layers[] = $layer;
	}

	/** @return Layer[] */
	public function &getLayers(): array {
		return $this->layers;
	}

	public function getNetwork(): BasicNetwork {
		return $this->network;
	}

	public function requireFlat(): FlatNetwork {
		if ($this->flat === null) {
			throw new NeuralNetworkError("Must call finalizeStructure before using this network.");
		}
		return $this->flat;
	}

	public function setFlat(FlatNetwork $flat) {
		$this->flat = $flat;
	}

	public function updateProperties() {
		if ($this->network->getProperty(BasicNetwork::TAG_LIMIT) !== null) {
			$this->connectionLimit = $this->network->getPropertyDouble(BasicNetwork::TAG_LIMIT);
			$this->connectionLimited = true;
		} else {
			$this->connectionLimited = false;
			$this->connectionLimit = 0.0;
		}
		if ($this->flat !== null) {
			$this->flat->setConnectionLimit($this->connectionLimit);
		}
	}
}
