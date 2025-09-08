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
namespace encog\neural\som\training\basic;

use encog\mathutil\matrices\Matrix;
use encog\mathutil\matrices\MatrixMath;
use encog\ml\data\basic\BasicMLData;
use encog\ml\data\MLData;
use encog\ml\data\MLDataPair;
use encog\ml\data\MLDataSet;
use encog\ml\MLMethod;
use encog\ml\train\BasicTraining;
use encog\ml\TrainingImplementationType;
use encog\neural\networks\training\LearningRate;
use encog\neural\networks\training\propagation\TrainingContinuation;
use encog\neural\NeuralNetworkError;
use encog\neural\som\SOM;
use encog\neural\som\training\basic\neighborhood\Func;
use encog\util\Format;
use encog\util\logging\EncogLogging;

/**
 * This class implements competitive training, which would be used in a
 * winner-take-all neural network, such as the self organizing map (SOM). This
 * is an unsupervised training method, no ideal data is needed on the training
 * set. If ideal data is provided, it will be ignored.
 *
 * Training is done by looping over all of the training elements and calculating
 * a "best matching unit" (BMU). This BMU output neuron is then adjusted to
 * better "learn" this pattern. Additionally, this training may be applied to
 * other "nearby" output neurons. The degree to which nearby neurons are update
 * is defined by the neighborhood function.
 *
 * A neighborhood function is required to determine the degree to which
 * neighboring neurons (to the winning neuron) are updated by each training
 * iteration.
 *
 * Because this is unsupervised training, calculating an error to measure
 * progress by is difficult. The error is defined to be the "worst", or longest,
 * Euclidean distance of any of the BMU's. This value should be minimized, as
 * learning progresses.
 *
 * Because only the BMU neuron and its close neighbors are updated, you can end
 * up with some output neurons that learn nothing. By default these neurons are
 * not forced to win patterns that are not represented well. This spreads out
 * the workload among all output neurons. This feature is not used by default,
 * but can be enabled by setting the "forceWinner" property.
 */
class BasicTrainSOM extends BasicTraining implements LearningRate {
	public function __construct(SOM $network, float $learningRate,
			?MLDataSet $training, Func $neighborhood
	) {
		parent::__construct(new TrainingImplementationType(TrainingImplementationType::Iterative));
		$this->neighborhood = $neighborhood;
		$this->network = $network;
		$this->learningRate = $learningRate;
		$this->inputNeuronCount = $network->getInputCount();
		$this->outputNeuronCount = $network->getOutputCount();
		$this->correctionMatrix = Matrix::createZero($this->outputNeuronCount, $this->inputNeuronCount);
		$this->bmu = new BestMatchingUnit($network);
		$this->forceWinner = false;
		if ($training) {
			$this->setTraining($training);
		}
		$this->setError(0.0);
	}

	public function __toString(): string {
		return sprintf("Rate=%s, Radius=%s",
			Format::formatDouble($this->learningRate, 2),
			Format::formatDouble($this->radius, 2)
		);
	}

	public function autoDecay() {
		if ($this->radius > $this->endRadius) {
			$this->radius += $this->autoDecayRadius;
		}
		if ($this->learningRate > $this->endRate) {
			$this->learningRate += $this->autoDecayRate;
		}
		$this->neighborhood->setRadius($this->radius);
	}

	public function getNeighborhood(): Func {
		return $this->neighborhood;
	}

	public function getInputNeuronCount(): int {
		return $this->inputNeuronCount;
	}

	public function getOutputNeuronCount(): int {
		return $this->outputNeuronCount;
	}

	public function getLearningRate(): float {
		return $this->learningRate;
	}

	public function setLearningRate(float $value) {
		$this->learningRate = $value;
	}

	public function canContinue(): bool {
		return false;
	}

	public function pause(): TrainingContinuation {
		throw new NeuralNetworkError("Training cannot be paused.");
	}

	public function resume(TrainingContinuation $state) {}

	public function getMethod(): MLMethod {
		return $this->network;
	}

	public function isForceWinner(): bool {
		return $this->forceWinner;
	}

	public function setForceWinner(bool $forceWinner) {
		$this->forceWinner = $forceWinner;
	}

	public function setAutoDecay(int $plannedIterations,
			float $startRate, float $endRate,
			float $startRadius, float $endRadius
	) {
		$this->autoDecayRadius = ($endRadius - $startRadius) / $plannedIterations;
		$this->autoDecayRate = ($endRate - $startRate) / $plannedIterations;
		$this->startRadius = $startRadius;
		$this->endRadius = $endRadius;
		$this->startRate = $startRate;
		$this->endRate = $endRate;

		$this->setParams($startRate, $startRadius);
	}

	public function setParams(float $rate, float $radius) {
		$this->neighborhood->setRadius($radius);
		$this->learningRate = $rate;
		$this->radius = $radius;
	}

	public function trainInputPattern(MLData $input) {
		$this->train($this->bmu->calculateBMU($input), $this->network->getWeights(), $input);
		$this->applyCorrection();
	}

	protected function doIteration() {
		EncogLogging::log(EncogLogging::LEVEL_INFO, "Performing SOM Training iteration.");
		$this->correctionMatrix->clear();
		$this->bmu->reset();

		$won = array_fill(0, $this->outputNeuronCount, 0);
		$leastRepresentedActivation = INF;
		$leastRepresented = null;
		/** @var MLDataPair $pair */
		foreach ($this->getTraining() as $pair) {
			$input = $pair->getInput();
			$bmu = $this->bmu->calculateBMU($input);
			$won[$bmu]++;

			if ($this->forceWinner) {
				$output = $this->compute($this->network, $input)->getDataAt($bmu);
				if ($output < $leastRepresentedActivation) {
					$leastRepresentedActivation = $output;
					$leastRepresented = $input;
				}
			}
			$this->train($bmu, $this->network->getWeights(), $input);

			if ($this->forceWinner) {
				if ($leastRepresented && !$this->forceWinners($this->network->getWeights(), $won, $leastRepresented)) {
					$this->applyCorrection();
				}
			} else {
				$this->applyCorrection();
			}
		}
		$this->setError($this->bmu->getWorstDistance() / 100.0);
	}

	private function applyCorrection() {
		$this->network->getWeights()->setFromMatrix($this->correctionMatrix);
	}

	private function compute(SOM $network, MLData $input): MLData {
		$result = new BasicMLData($network->getOutputCount());
		for ($i = 0; $i < $network->getOutputCount(); $i++) {
			$result->setDataAt($i, MatrixMath::dotProduct(
				Matrix::createRowMatrix($input->getData()->toArray()),
				$network->getWeights()->getRow($i)
			));
		}
		return $result;
	}

	private function copyInputPattern(Matrix $matrix, int $output, MLData $input) {
		for ($i = 0; $i < $this->inputNeuronCount; $i++) {
			$matrix->setRowCol($output, $i, $input->getDataAt($i));
		}
	}

	private function determineNewWeight(float $weight, float $input, int $neuron, int $bmu): float {
		return $weight + ($this->neighborhood->calculate($neuron, $bmu) * $this->learningRate * ($input-$weight));
	}

	private function forceWinners(Matrix $matrix, array $won, MLData $least): bool {
		$maxActivationNeuron = -1;
		$maxActivation = NAN;
		$output = $this->compute($this->network, $least);

		foreach ($won as $index => $wins) {
			$activation = $output->getDataAt($index);
			if ($wins == 0 && ($maxActivationNeuron == -1 || $activation > $maxActivation)) {
				$maxActivationNeuron = $index;
				$maxActivation = $activation;
			}
		}
		if ($maxActivationNeuron != -1) {
			$this->copyInputPattern($matrix, $maxActivationNeuron, $least);
			return true;
		}
		return false;
	}

	private function train(int $bmu, Matrix $matrix, MLData $input) {
		for ($i = 0; $i < $this->outputNeuronCount; $i++) {
			$this->trainPatternMatrix($matrix, $input, $i, $bmu);
		}
	}

	private function trainPatternMatrix(Matrix $matrix, MLData $input, int $current, int $bmu) {
		for ($i = 0; $i < $this->inputNeuronCount; $i++) {
			$this->correctionMatrix->setRowCol($current, $i, $this->determineNewWeight(
				$matrix->get($current, $i),
				$input->getDataAt($i),
				$current,
				$bmu
			));
		}
	}

	/** @var int */
	private $inputNeuronCount, $outputNeuronCount;

	/** @var float */
	private $autoDecayRate = 0.0, $autoDecayRadius = 0.0;

	/** @var float */
	private $startRate, $endRate;

	/** @var float */
	private $startRadius, $endRadius;

	/** @var float */
	private $learningRate;

	/** @var float */
	private $radius = 0.0;

	/** @var bool */
	private $forceWinner;

	/** @var Matrix */
	private $correctionMatrix;

	/** @var Func */
	private $neighborhood;

	/** @var SOM */
	private $network;

	/** @var BestMatchingUnit */
	private $bmu;
}
