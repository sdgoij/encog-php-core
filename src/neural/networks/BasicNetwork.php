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
namespace encog\neural\networks;

use encog\engine\network\activation\ActivationElliott;
use encog\engine\network\activation\ActivationElliottSymmetric;
use encog\engine\network\activation\ActivationFunction;
use encog\engine\network\activation\ActivationSigmoid;
use encog\engine\network\activation\ActivationTANH;
use encog\mathutil\randomize\ConsistentRandomizer;
use encog\mathutil\randomize\NguyenWidrowRandomizer;
use encog\mathutil\randomize\Randomizer;
use encog\mathutil\randomize\RangeRandomizer;
use encog\ml\BasicML;
use encog\ml\data\basic\BasicMLData;
use encog\ml\data\MLData;
use encog\ml\data\MLDataSet;
use encog\ml\factory\MLMethodFactory;
use encog\ml\MLClassification;
use encog\ml\MLContext;
use encog\ml\MLEncodable;
use encog\ml\MLError;
use encog\ml\MLFactory;
use encog\ml\MLRegression;
use encog\ml\MLResettable;
use encog\neural\flat\FlatNetwork;
use encog\neural\networks\layers\Layer;
use encog\neural\networks\structure\NeuralStructure;
use encog\neural\NeuralNetworkError;
use encog\util\simple\EncogUtility;
use SplFixedArray;

/**
 * This class implements a neural network. This class works in conjunction the
 * Layer classes. Layers are added to the BasicNetwork to specify the structure
 * of the neural network.
 *
 * The first layer added is the input layer, the final layer added is the output
 * layer. Any layers added between these two layers are the hidden layers.
 *
 * The network structure is stored in the structure member. It is important to
 * call network.getStructure().finalizeStructure() once the neural network has
 * been completely constructed.
 */
class BasicNetwork extends BasicML implements ContainsFlat, MLContext, MLRegression,
		MLEncodable, MLResettable, MLClassification, MLError, MLFactory {

	const DEFAULT_CONNECTION_LIMIT = 0.0000000001;
	const TAG_LIMIT = "CONNECTION_LIMIT";

	/** @var NeuralStructure */
	private $structure;

	public function __construct() {
		$this->structure = new NeuralStructure($this);
	}

	public function __toString() {
		return sprintf("[BasicNetwork: Layers=%d]", $this->structure->getFlat()
			? count($this->structure->getFlat()->getLayerCounts())
			: count($this->structure->getLayers())
		);
	}

	public function addLayer(Layer $layer) {
		$this->structure->addLayer($layer);
		$layer->setNetwork($this);
	}

	public function addWeight(int $fromLayer, int $fromNeuron, int $toNeuron, float $value) {
		$this->setWeight($fromLayer, $fromNeuron, $toNeuron, $this->getWeight($fromLayer, $fromNeuron, $toNeuron)+$value);
	}

	public function getWeight(int $fromLayer, int $fromNeuron, int $toNeuron): float {
		return $this->structure->requireFlat()->getWeight($fromLayer, $fromNeuron, $toNeuron);
	}

	public function setWeight(int $fromLayer, int $fromNeuron, int $toNeuron, float $value) {
		$flat = $this->structure->requireFlat();
		$fromLayerNumber = $this->getLayerCount() - $fromLayer - 1;
		$toLayerNumber = $fromLayerNumber - 1;

		if ($toLayerNumber < 0) {
			throw new NeuralNetworkError("The specified layer is not connected to another layer: $fromLayer");
		}

		$base = $flat->getWeightIndex()[$toLayerNumber];
		$count = $flat->getLayerCounts()[$fromLayerNumber];
		$index = $base + $fromNeuron + ($toNeuron * $count);
		$flat->getWeights()[$index] = $value;
	}

	public function isLayerBiased(int $index): bool {
		$flat = $this->structure->requireFlat();
		$index = $this->getLayerCount() - $index - 1;
		return $flat->getLayerCounts()[$index] != $flat->getLayerFeedCounts()[$index];
	}

	public function setBiasActivation(float $value) {
		if (!$this->structure->getFlat()) {
			foreach ($this->structure->getLayers() as $layer) {
				if ($layer->hasBias()) {
					$layer->setBiasActivation($value);
				}
			}
		} else {
			for ($i = 0; $i < $this->getLayerCount(); $i++) {
				if ($this->isLayerBiased($i)) {
					$this->setLayerBiasActivation($i, $value);
				}
			}
		}
	}

	public function setLayerBiasActivation(int $index, float $value) {
		if (!$this->isLayerBiased($index)) {
			throw new NeuralNetworkError("Error, the specified layer does not have a bias: $index");
		}

		$flat = $this->structure->requireFlat();
		$index = $this->getLayerCount() - $index - 1;
		$output = $flat->getLayerIndex()[$index];
		$count = $flat->getLayerCounts()[$index];

		$flat->getLayerOutput()[$output+$count-1] = $value;
	}

	public function updateProperties() {
		$this->structure->updateProperties();
	}

	public function clearContext() {
		if ($flat = $this->structure->getFlat()) {
			$flat->clearContext();
		}
	}

	public function calculateError(MLDataSet $data): float {
		return EncogUtility::calculateRegressionError($this, $data);
	}

	public function getLayerCount(): int {
		return count($this->structure->requireFlat()->getLayerCounts());
	}

	public function getLayerNeuronCount(int $index): int {
		$index = $this->getLayerCount() - $index - 1;
		return $this->structure->requireFlat()->getLayerFeedCounts()[$index];
	}

	public function getLayerOutput(int $layer, int $neuron): float {
		$index = $this->structure->requireFlat()->getLayerIndex()[$this->getLayerCount()-$layer-1]+$neuron;
		$output = $this->structure->getFlat()->getLayerOutput();
		if ($index >= count($output)) {
			throw new NeuralNetworkError("The layer index: $index specifies an output index larger than the network has.");
		}
		return $output[$index];
	}

	public function getInputCount(): int {
		return $this->structure->requireFlat()->getInputCount();
	}

	public function getOutputCount(): int {
		return $this->structure->requireFlat()->getOutputCount();
	}

	public function getLayerTotalNeuronCount(int $layer): int {
		return $this->structure->requireFlat()->getLayerCounts()[$this->getLayerCount()-$layer-1];
	}

	public function calculateNeuronCount(): int {
		$result = 0;
		foreach ($this->structure->getLayers() as $layer) {
			$result += $layer->getNeuronCount();
		}
		return $result;
	}

	public function classify(MLData $input): int {
		return $this->winner($input);
	}

	public function reset(?int $seed = null) {
		if ($seed !== null) {
			(new ConsistentRandomizer(-1, 1, $seed))->randomize($this);
		} else {
			$this->getRandomizer()->randomize($this);
		}
	}

	private function getRandomizer(): Randomizer {
		for ($i = 0; $i < $this->getLayerCount(); $i++) {
			$af = $this->getActivation($i);
			if (!$af instanceof ActivationElliottSymmetric &&
					!$af instanceof ActivationElliott &&
					!$af instanceof ActivationSigmoid &&
					!$af instanceof ActivationTANH) {
				$useRangeRandomizer = true;
			}
		}
		return ($useRangeRandomizer ?? false)
			? new RangeRandomizer(-1.0, 1.0)
			: new NguyenWidrowRandomizer();
	}

	public function getActivation(int $index): ActivationFunction {
		$index = $this->getLayerCount() - $index - 1;
		return $this->structure->requireFlat()->getActivationFunctions()[$index];
	}

	public function compute(MLData $input): MLData {
		$output = new SplFixedArray($this->getOutputCount());
		$this->structure->requireFlat()->compute($input->getData()->toArray(), $output);
		return new BasicMLData($output);
	}

	public function computeArray(array $input, array &$output) {
		$output = $this->compute(new BasicMLData($input))->getData()->toArray();
	}

	public function getFactoryType(): string {
		return MLMethodFactory::TYPE_FEEDFORWARD;
	}

	public function getFactoryArchitecture(): string {
		$result = "";
		for ($i = 0; $i < $this->getLayerCount(); $i++) {
			if ($i > 0) {
				$result .= "->";
				// @phpstan-ignore if.alwaysTrue
				if ($af = $this->getActivation($i)) {
					$result .= $af->getFactoryCode();
					$result .= "->";
				}
			}
			$result .= $this->getLayerNeuronCount($i);
			if ($this->isLayerBiased($i)) {
				$result .= ":B";
			}
		}
		return $result;
	}

	public function enableConnection(int $layer, int $from, int $to, bool $enable) {
		if ($enable) {
			$value = abs($this->getWeight($layer, $from, $to));
			if ($this->structure->isConnectionLimited() && $value < $this->structure->getConnectionLimit()) {
				$this->setWeight($layer, $from, $to, RangeRandomizer::randomFloat(-1, 1));
			}
		} else {
			if (!$this->structure->isConnectionLimited()) {
				$this->setProperty(BasicNetwork::TAG_LIMIT, BasicNetwork::DEFAULT_CONNECTION_LIMIT);
				$this->structure->updateProperties();
			}
			$this->setWeight($layer, $from, $to, 0);
		}
	}

	public function encodedArrayLength(): int {
		return $this->structure->requireFlat()->getEncodeLength();
	}

	public function encodeToArray(array &$data) {
		$data = $this->structure->requireFlat()->getWeights()->toArray();
	}

	public function decodeFromArray(array $data) {
		$this->structure->requireFlat()->decodeNetwork($data);
	}

	public function getFlat(): FlatNetwork {
		return $this->structure->getFlat();
	}

	public function validateNeuron(int $layer, int $neuron) {
		$this->structure->requireFlat()->validateNeuron($layer, $neuron);
	}

	public function winner(MLData $input): int {
		return self::maxIndex($this->compute($input)->getData());
	}

	public static function maxIndex($data): int {
		if (!$data instanceof \Traversable && !is_array($data))
			throw new \InvalidArgumentException;
		$result = -1;
		foreach ($data as $k => $v) {
			if ($result == -1 || $v > $data[$result]) {
				$result = $k;
			}
		}
		return $result;
	}

	public function getStructure(): NeuralStructure {
		return $this->structure;
	}
}
