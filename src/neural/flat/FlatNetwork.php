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
namespace encog\neural\flat;

use InvalidArgumentException;

use encog\EncogError;
use encog\engine\network\activation\ActivationFunction;
use encog\engine\network\activation\ActivationLinear;
use encog\engine\network\activation\ActivationSigmoid;
use encog\engine\network\activation\ActivationTANH;
use encog\mathutil\error\ErrorCalculation;
use encog\ml\data\basic\BasicMLDataPair;
use encog\ml\data\MLDataSet;
use encog\neural\NeuralNetworkError;
use SplFixedArray;

/**
 * Implements a flat (vector based) neural network in the Encog Engine. This is
 * meant to be a very highly efficient feedforward, or simple recurrent, neural
 * network. It uses a minimum of objects and is designed with one principal in
 * mind-- SPEED. Readability, code reuse, object oriented programming are all
 * secondary in consideration.
 *
 * // Vector based neural networks are also very good for GPU processing. The flat
 * // network classes will make use of the GPU if you have enabled GPU processing.
 * // See the Encog class for more info.
 */
class FlatNetwork {
	const DEFAULT_BIAS_ACTIVATION = 1.0;
	const NO_BIAS_ACTIVATION      = 0.0;

	/** @var int */
	private $inputCount;

	/** @var SplFixedArray  */
	private $layerCounts;

	/** @var SplFixedArray */
	private $layerDropoutRates;

	/** @var SplFixedArray */
	private $layerContextCount;

	/** @var SplFixedArray */
	private $layerFeedCounts;

	/** @var SplFixedArray */
	private $layerIndex;

	/** @var SplFixedArray */
	private $layerOutput;

	/** @var SplFixedArray */
	private $layerSums;

	/** @var int */
	private $outputCount;

	/** @var SplFixedArray */
	private $weightIndex;

	/** @var SplFixedArray */
	private $weights;

	/** @var ActivationFunction[] */
	private $activationFunctions = [];

	/** @var SplFixedArray */
	private $contextTargetOffset;

	/** @var SplFixedArray */
	private $contextTargetSize;

	/** @var SplFixedArray */
	private $biasActivation;

	/** @var int */
	private $beginTraining;

	/** @var int */
	private $endTraining;

	/** @var bool */
	private $isLimited;

	/** @var float */
	private $connectionLimit;

	/** @var bool */
	private $hasContext = false;

	public static function createFromArray(array $layers, bool $dropout = false): FlatNetwork {
		$network = new static();
		$network->init($layers, $dropout);
		return $network;
	}

	public function __construct(int $input = 0, int $hidden1 = 0, int $hidden2 = 0, int $output = 0, bool $tanh = false) {
		$activation = $tanh ? new ActivationTANH() : new ActivationSigmoid();
		$linear = new ActivationLinear();
		$this->connectionLimit = 0.0;
		$this->isLimited = false;
		$layers = [];

		if ($input && $output) {
			if (!$hidden1 && !$hidden2) {
				$layers[] = new FlatLayer($linear, $input, self::DEFAULT_BIAS_ACTIVATION);
				$layers[] = new FlatLayer($activation, $output, self::NO_BIAS_ACTIVATION);
			} else if (!$hidden1 || !$hidden2) {
				$layers[] = new FlatLayer($linear, $input, self::DEFAULT_BIAS_ACTIVATION);
				$layers[] = new FlatLayer($activation, max($hidden1, $hidden2), self::DEFAULT_BIAS_ACTIVATION);
				$layers[] = new FlatLayer($activation, $output, self::NO_BIAS_ACTIVATION);
			} else {
				$layers[] = new FlatLayer($linear, $input, self::DEFAULT_BIAS_ACTIVATION);
				$layers[] = new FlatLayer($activation, $hidden1, self::DEFAULT_BIAS_ACTIVATION);
				$layers[] = new FlatLayer($activation, $hidden2, self::DEFAULT_BIAS_ACTIVATION);
				$layers[] = new FlatLayer($activation, $output, self::NO_BIAS_ACTIVATION);
			}
			$this->init($layers, false);
		}
	}

	/**
	 * @param FlatLayer[] $layers
	 * @param bool $dropout
	 * @throws InvalidArgumentException
	 */
	public function init(array $layers, bool $dropout) {
		if (count($layers) < 2) {
			throw new InvalidArgumentException("\$layers should at least contain a input and output layer");
		}
		$layerCount = count($layers);
		$this->inputCount = $layers[0]->getCount();
		$this->outputCount = $layers[$layerCount-1]->getCount();
		$this->layerCounts = new SplFixedArray($layerCount);
		$this->layerContextCount = new SplFixedArray($layerCount);
		$this->weightIndex = new SplFixedArray($layerCount);
		$this->layerIndex = new SplFixedArray($layerCount);
		$this->layerDropoutRates = new SplFixedArray($dropout ? $layerCount : 0);
		$this->layerFeedCounts = new SplFixedArray($layerCount);
		$this->contextTargetOffset = new SplFixedArray($layerCount);
		$this->contextTargetSize = new SplFixedArray($layerCount);
		$this->biasActivation = new SplFixedArray($layerCount);

		$index = 0;
		$neuronCount = 0;
		$weightCount = 0;

		for ($i = $layerCount-1; $i >= 0; $i--) {
			$this->biasActivation[$index] = $layers[$i]->getBiasActivation();
			$this->layerCounts[$index] = $layers[$i]->getTotalCount();
			$this->layerFeedCounts[$index] = $layers[$i]->getCount();
			$this->layerContextCount[$index] = $layers[$i]->getContextCount();
			$this->activationFunctions[$index] = $layers[$i]->getActivation();
			if ($dropout) {
				$this->layerDropoutRates[$index] = $layers[$i]->getDropoutRate();
			}
			if ($i > 0) {
				$weightCount += $layers[$i]->getCount() * $layers[$i-1]->getTotalCount();
			}
			$neuronCount += $layers[$i]->getTotalCount();

			if ($index > 0) {
				$this->weightIndex[$index] = $this->weightIndex[$index-1]
					+ $this->layerCounts[$index] * $this->layerFeedCounts[$index-1];
				$this->layerIndex[$index] = $this->layerIndex[$index-1]
					+ $this->layerCounts[$index-1];
			} else {
				$this->weightIndex[$index] = 0;
				$this->layerIndex[$index] = 0;
			}
			$neuronIndex = 0;
			for ($j = $layerCount-1; $j >= 0; $j--) {
				if ($layers[$j]->getContextFedBy() === $layers[$i]) {
					$this->contextTargetOffset[$index] = $neuronIndex + $layers[$j]->getTotalCount()
						- $layers[$j]->getContextCount();
					$this->contextTargetSize[$index] = $layers[$j]->getContextCount();
					$this->hasContext = true;
				}
				$neuronIndex += $layers[$j]->getTotalCount();
			}
			$index++;
		}
		$this->beginTraining = 0;
		$this->endTraining = count($this->layerCounts) - 1;

		$this->weights = SplFixedArray::fromArray(array_fill(0, $weightCount, 0.0));
		$this->layerOutput = new SplFixedArray($neuronCount);
		$this->layerSums = new SplFixedArray($neuronCount);

		$this->clearContext();
	}

	public function clearContext() {
		for ($i = 0, $index = 0; $i < count($this->layerIndex); $i++) {
			self::fill($this->layerOutput, $index, $index+$this->layerFeedCounts[$i], 0.0);
			$index += $this->layerFeedCounts[$i];
			if ($this->layerContextCount[$i]+$this->layerFeedCounts[$i] != $this->layerCounts[$i]) {
				$this->layerOutput[$index++] = $this->biasActivation[$i];
			}
			self::fill($this->layerOutput, $index, $index+$this->layerContextCount[$i], 0.0);
			$index += $this->layerContextCount[$i];
		}
	}

	private static function fill(&$data, int $from, int $to, float $value = 0.0) {
		while ($from < $to) $data[$from++] = $value;
	}

	private static function copy($source, int $start, &$dest, int $offset, int $length) {
		for ($i = 0; $i < $length; $i++) $dest[$offset+$i] = $source[$start+$i];
	}

	public function calculateError(MLDataSet $data): float {
		$error = new ErrorCalculation();
		$actual = new SplFixedArray($this->outputCount);
		$pair = BasicMLDataPair::createPair(
				$data->getInputSize(), $data->getIdealSize());
		$count = $data->getRecordCount();
		for ($i = 0; $i < $count; $i++) {
			$data->getRecord($i, $pair);
			$this->compute($pair->getInputArray(), $actual);
			$error->updateErrorArray($actual,
				$pair->getIdealArray(),
				$pair->getSignificance()
			);
		}
		return $error->calculate();
	}

	public function clearConnectionLimit() {
		$this->connectionLimit = 0.0;
		$this->isLimited = false;
	}

	public function compute(array $input, SplFixedArray $output) {
		$sourceIndex = count($this->layerOutput) - $this->layerCounts[count($this->layerCounts)-1];
		self::copy($input, 0, $this->layerOutput, $sourceIndex, $this->inputCount);

		for ($i = count($this->layerIndex)-1; $i > 0; $i--) {
			$this->computeLayer($i);
		}

		self::copy($this->layerOutput, 0, $this->layerOutput, $this->contextTargetOffset[0] ?? 0, $this->contextTargetSize[0] ?? 0);
		self::copy($this->layerOutput, 0, $output, 0, $this->outputCount);
	}

	protected function computeLayer(int $currentLayer) {
		$inputIndex = $this->layerIndex[$currentLayer];
		$outputIndex = $this->layerIndex[$currentLayer-1];
		$inputSize = $this->layerCounts[$currentLayer];
		$outputSize = $this->layerFeedCounts[$currentLayer-1];
		try {
			$dropoutRate = $this->layerDropoutRates[$currentLayer - 1];
		} catch (\RuntimeException $e) {
			$dropoutRate = 0;
		}
		$index = $this->weightIndex[$currentLayer-1];
		$limitX = $outputIndex + $outputSize;
		$limitY = $inputIndex + $inputSize;
		for ($i = $outputIndex; $i < $limitX; $i++) {
			$sum = 0;
			for ($j = $inputIndex; $j < $limitY; $j++) {
				$sum += $this->weights[$index++] * $this->layerOutput[$j] * (1-$dropoutRate);
			}
			$this->layerOutput[$i] = $sum;
			$this->layerSums[$i] = $sum;
		}
		$this->activationFunctions[$currentLayer-1]->activationFunction(
			$this->layerOutput, $outputIndex, $outputSize);
		$offset = $this->contextTargetOffset[$currentLayer] ?? 0;
		$size = $this->contextTargetSize[$currentLayer] ?? 0;
		self::copy($this->layerOutput, $outputIndex,
			$this->layerOutput, $offset, $size);
	}

	public function getWeight(int $fromLayer, int $fromNeuron, int $toNeuron): float {
		$this->validateNeuron($fromLayer, $fromNeuron)->validateNeuron($fromLayer+1, $toNeuron);
		$fromLayerNumber = count($this->layerContextCount) - $fromLayer - 1;
		$toLayerNumber = $fromLayerNumber - 1;
		assert($toLayerNumber >= 0);

		$base = $this->weightIndex[$toLayerNumber];
		$count = $this->layerCounts[$fromLayerNumber];
		$index = $base + $fromNeuron + ($toNeuron * $count);
		return $this->weights[$index];
	}

	public function validateNeuron(int $layer, int $neuron) {
		if ($layer < 0 || $layer >= count($this->layerCounts)) {
			throw new NeuralNetworkError("Invalid layer count: $layer");
		}
		if ($neuron < 0 || $neuron >= $this->getLayerTotalNeuronCount($layer)) {
			throw new NeuralNetworkError("Invalid neuron number: $neuron");
		}
		return $this;
	}

	public function randomize(float $high = 1.0, float $low = -1.0) {
		for ($i = 0; $i < count($this->weights); $i++) {
			$this->weights[$i] = mt_rand()/mt_getrandmax() * ($high-$low) + $low;
		}
	}

	public function getLayerTotalNeuronCount(int $index): int {
		return $this->layerCounts[count($this->layerCounts)-$index-1];
	}

	public function getLayerNeuronCount(int $index): int {
		return $this->layerFeedCounts[count($this->layerCounts)-$index-1];
	}

	public function decodeNetwork(array $data) {
		if (count($data) != count($this->weights)) {
			throw new EncogError(sprintf(
				"Incompatible weight sizes, can't assign length=%d to length=%d",
				count($data), count($this->weights)
			));
		}
		$this->weights = SplFixedArray::fromArray($data);
	}

	public function encodeNetwork(): array {
		return $this->weights->toArray();
	}

	public function getEncodeLength(): int {
		return count($this->weights);
	}

	public function getNeuronCount(): int {
		return array_sum($this->layerCounts->toArray());
	}

	/** @return ActivationFunction[] */
	public function getActivationFunctions(): array {
		return $this->activationFunctions;
	}

	public function setActivationFunctions(ActivationFunction ...$f) {
		$this->activationFunctions = $f;
	}

	public function getBeginTraining(): int {
		return $this->beginTraining;
	}

	public function setBeginTraining(int $v) {
		$this->beginTraining = $v;
	}

	public function getEndTraining(): int {
		return $this->endTraining;
	}

	public function setEndTraining(int $v) {
		$this->endTraining = $v;
	}

	public function getBiasActivation(): SplFixedArray {
		return $this->biasActivation;
	}

	public function setBiasActivation(SplFixedArray $v) {
		$this->biasActivation = $v;
	}

	public function getConnectionLimit(): float {
		return $this->connectionLimit;
	}

	public function setConnectionLimit(float $v) {
		$this->connectionLimit = $v;
	}

	public function isLimited(): bool {
		return $this->isLimited;
	}

	public function setLimited(bool $v) {
		$this->isLimited = $v;
	}

	public function getContextTargetOffset(): SplFixedArray {
		return $this->contextTargetOffset;
	}

	public function setContextTargetOffset(SplFixedArray $v) {
		$this->contextTargetOffset = $v;
	}

	public function getContextTargetSize(): SplFixedArray {
		return $this->contextTargetSize;
	}

	public function setContextTargetSize(SplFixedArray $v) {
		$this->contextTargetSize = $v;
	}

	public function getHasContext(): bool {
		return $this->hasContext;
	}

	public function setHasContext(bool $v) {
		$this->hasContext = $v;
	}

	public function getInputCount(): int {
		return $this->inputCount;
	}

	public function setInputCount(int $v) {
		$this->inputCount = $v;
	}

	public function getOutputCount(): int {
		return $this->outputCount;
	}

	public function setOutputCount(int $v) {
		$this->outputCount = $v;
	}

	public function getLayerCounts(): SplFixedArray {
		return $this->layerCounts;
	}

	public function setLayerCounts(SplFixedArray $layerCounts) {
		$this->layerCounts = $layerCounts;
	}

	public function getLayerDropoutRates(): SplFixedArray {
		return $this->layerDropoutRates;
	}

	public function setLayerDropoutRates(SplFixedArray $layerDropoutRates) {
		$this->layerDropoutRates = $layerDropoutRates;
	}

	public function getLayerContextCount(): SplFixedArray {
		return $this->layerContextCount;
	}

	public function setLayerContextCount(SplFixedArray $layerContextCount) {
		$this->layerContextCount = $layerContextCount;
	}

	public function getLayerFeedCounts(): SplFixedArray {
		return $this->layerFeedCounts;
	}

	public function setLayerFeedCounts(SplFixedArray $layerFeedCounts) {
		$this->layerFeedCounts = $layerFeedCounts;
	}

	public function getLayerIndex(): SplFixedArray {
		return $this->layerIndex;
	}

	public function setLayerIndex(SplFixedArray $layerIndex) {
		$this->layerIndex = $layerIndex;
	}

	public function getLayerOutput(): SplFixedArray {
		return $this->layerOutput;
	}

	public function setLayerOutput(SplFixedArray $layerOutput) {
		$this->layerOutput = $layerOutput;
	}

	public function getLayerSums(): SplFixedArray {
		return $this->layerSums;
	}

	public function setLayerSums(SplFixedArray $layerSums) {
		$this->layerSums = $layerSums;
	}

	public function getWeightIndex(): SplFixedArray {
		return $this->weightIndex;
	}

	public function setWeightIndex(SplFixedArray $weightIndex) {
		$this->weightIndex = $weightIndex;
	}

	public function getWeights(): SplFixedArray {
		return $this->weights;
	}

	public function setWeights(SplFixedArray $weights) {
		$this->weights = $weights;
	}
}
