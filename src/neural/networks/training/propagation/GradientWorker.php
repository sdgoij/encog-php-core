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
namespace encog\neural\networks\training\propagation;

use encog\Encog;
use encog\mathutil\error\ErrorCalculation;
use encog\ml\data\basic\BasicMLDataPair;
use encog\ml\data\MLDataPair;
use encog\ml\data\MLDataSet;
use encog\neural\error\ErrorFunction;
use encog\neural\flat\FlatNetwork;
use encog\util\concurrency\EngineTask;
use encog\util\Random;
use SplFixedArray;
use Throwable;

/**
 * Worker class for the training of flat networks.
 */
class GradientWorker implements EngineTask {
	/** @var Random */
	private $dropoutRandomSource;

	/** @var FlatNetwork */
	private $network;

	/** @var ErrorCalculation */
	private $errCalc;

	/** @var ErrorFunction */
	private $errFunc;

	/** @var SplFixedArray */
	private $actual;

	/** @var SplFixedArray */
	private $layerDelta;

	/** @var SplFixedArray */
	private $layerCounts;

	/** @var SplFixedArray */
	private $layerFeedCounts;

	/** @var SplFixedArray */
	private $layerIndex;

	/** @var SplFixedArray */
	private $weightIndex;

	/** @var SplFixedArray */
	private $layerOutput;

	/** @var SplFixedArray */
	private $layerSums;

	/** @var SplFixedArray */
	private $gradients;

	/** @var SplFixedArray */
	private $weights;

	/** @var MLDataPair */
	private $pair;

	/** @var MLDataSet */
	private $training;

	/** @var int */
	private $low;

	/** @var int */
	private $high;

	/** @var GradientWorkerOwner */
	private $owner;

	/** @var float[] */
	private $flatSpot;

	/** @var SplFixedArray */
	private $layerDropoutRates;

	public function isGarbage(): bool { return $this->done ?? false; }

	public function __construct(
			FlatNetwork $network,
			GradientWorkerOwner $owner,
			MLDataSet $training,
			int $low,
			int $high,
			array $flatSpot,
			ErrorFunction $errFunc
	) {
		$this->dropoutRandomSource = new Random();
		$this->errCalc = new ErrorCalculation();

		$this->network = $network;
		$this->training = $training;
		$this->low = $low;
		$this->high = $high;
		$this->owner = $owner;
		$this->flatSpot = $flatSpot;
		$this->errFunc = $errFunc;

		$this->layerDelta = SplFixedArray::fromArray(array_fill(0, count($network->getLayerOutput()), 0.0));
		$this->gradients = SplFixedArray::fromArray(array_fill(0, count($network->getWeights()), 0.0));
		$this->actual = SplFixedArray::fromArray(array_fill(0, $network->getOutputCount(), 0.0));

		$this->layerIndex = $network->getLayerIndex();
		$this->layerCounts = $network->getLayerCounts();
		$this->layerDropoutRates = $network->getLayerDropoutRates();
		$this->layerOutput = $network->getLayerOutput();
		$this->layerSums = $network->getLayerSums();
		$this->layerFeedCounts = $network->getLayerFeedCounts();
		$this->weightIndex = $network->getWeightIndex();
		$this->weights = $network->getWeights();

		$this->pair = BasicMLDataPair::createPair(
			$network->getInputCount(),
			$network->getOutputCount()
		);
	}

	public function process(MLDataPair $pair) {
		$this->network->compute($pair->getInputArray(), $this->actual);
		$this->errCalc->updateErrorArray(
			$this->actual->toArray(),
			$pair->getIdealArray(),
			$pair->getSignificance()
		);
		$this->errFunc->calculateError(
			$this->network->getActivationFunctions()[0],
			$this->layerSums->toArray(),
			$this->layerOutput->toArray(),
			$pair->getIdealArray(),
			$this->actual->toArray(),
			$this->layerDelta,
			$this->flatSpot[0] ?? 0.0,
			$pair->getSignificance()
		);
		if ($this->owner->getL1() > Encog::DEFAULT_DOUBLE_EQUAL ||
				$this->owner->getL2() > Encog::DEFAULT_DOUBLE_EQUAL) {
			$lp = $this->calculateRegularizationPenalty();
			for ($i = 0; $i < $this->actual->getSize(); $i++) {
				$this->layerDelta[$i] += $lp[0] * $this->owner->getL1();
				$this->layerDelta[$i] += $lp[1] * $this->owner->getL2();
			}
		}
		for ($i = $this->network->getBeginTraining();
				 $i < $this->network->getEndTraining();) {
			$this->processLevel($i++);
		}
	}

	public function processLevel(int $currentLevel) {
		$fromLayerIndex = $this->layerIndex[$currentLevel+1];
		$toLayerIndex = $this->layerIndex[$currentLevel];
		$fromLayerSize = $this->layerCounts[$currentLevel+1];
		$toLayerSize = $this->layerFeedCounts[$currentLevel];
		$dropoutRate = $this->layerDropoutRates->getSize() > $currentLevel
			? $this->layerDropoutRates[$currentLevel] : 0.0;
		$index = $this->weightIndex[$currentLevel];
		$activation = $this->network->getActivationFunctions()[$currentLevel];
		$flatSpot = $this->flatSpot[$currentLevel+1] ?? 0;
		$layerDelta = $this->layerDelta;
		$weights = $this->weights;
		$gradients = $this->gradients;
		$layerOutput = $this->layerOutput;
		$layerSums = $this->layerSums;
		for ($y = 0; $y < $fromLayerSize; $y++) {
			$output = $layerOutput[$fromLayerIndex];
			$sum = 0;
			$wi = $index+$y;
			if ($dropoutRate == 0 || $this->dropoutRandomSource->nextDouble() > $dropoutRate) {
				for ($xi = $toLayerIndex; $xi < $toLayerIndex+$toLayerSize; $wi += $fromLayerSize, $xi++) {
					$gradients[$wi] = $gradients[$wi] + $output * $layerDelta[$xi];
					$sum += $weights[$wi] * $layerDelta[$xi];
				}
				$layerDelta[$fromLayerIndex] = $sum * ($activation->derivativeFunction(
					$layerSums[$fromLayerIndex] ?? 0.0, $layerOutput[$fromLayerIndex]
				) + $flatSpot);
			} else {
				$layerDelta[$fromLayerIndex] = 0;
			}
			$fromLayerIndex++;
		}
	}

	public function run() {
		try {
			$this->errCalc->reset();
			// FIXME Cloning the training data set??
			$this->training = clone $this->training;
			for ($i = $this->low; $i <= $this->high; $i++) {
				$this->training->getRecord($i, $this->pair);
				$this->process($this->pair);
			}
			$this->owner->report($this->gradients->toArray(), $this->errCalc->calculate());
			self::fill($this->gradients, 0.0);
		} catch (Throwable $e) {
			$this->owner->report([], 0, $e);
		}
		$this->done = true;
	}

	public function runOne(int $index) {
		$this->training->getRecord($index, $this->pair);
		$this->process($this->pair);
		$this->owner->report($this->gradients->toArray(), 0);
		self::fill($this->gradients, 0.0);
	}

	public function calculateRegularizationPenalty(): array {
		$lp = [0.0, 0.0];
		$length = $this->network->getLayerCounts()->getSize();
		for ($i = 0; $i < $length-1; $i++) {
			$this->layerRegularizationPenalty($i, $lp);
		}
		return $lp;
	}

	public function layerRegularizationPenalty(int $layer, array &$l) {
		$from = $this->network->getLayerTotalNeuronCount($layer);
		$to = $this->network->getLayerNeuronCount($layer+1);
		for ($i = 0; $i < $from; $i++) {
			for ($j = 0; $j < $to; $j++) {
				$weight = $this->network->getWeight($layer, $i, $j);
				$l[0] += abs($weight);
				$l[1] += $weight*$weight;
			}
		}
	}

	public function getOwner(): GradientWorkerOwner {
		return $this->owner;
	}

	public function setOwner(GradientWorkerOwner $owner) {
		$this->owner = $owner;
	}

	public function getErrorCalculation(): ErrorCalculation {
		return $this->errCalc;
	}

	public function getGradients(): SplFixedArray {
		return $this->gradients;
	}

	public function getNetwork(): FlatNetwork {
		return $this->network;
	}

	public function getWeights(): SplFixedArray {
		return $this->weights;
	}

	public function setWeights(SplFixedArray $weights) {
		$this->weights = $weights;
	}

	private static function fill(SplFixedArray $data, float $value) {
		for ($i = 0; $i < $data->getSize();) $data[$i++] = $value;
	}
}
