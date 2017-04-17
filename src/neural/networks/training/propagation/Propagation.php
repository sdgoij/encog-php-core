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
namespace encog\neural\networks\training\propagation;

use encog\EncogError;
use encog\engine\network\activation\ActivationSigmoid;
use encog\ml\data\MLDataSet;
use encog\ml\MLMethod;
use encog\ml\train\BasicTraining;
use encog\ml\train\MLTrain;
use encog\ml\TrainingImplementationType;
use encog\neural\error\ErrorFunction;
use encog\neural\error\LinearErrorFunction;
use encog\neural\flat\FlatNetwork;
use encog\neural\networks\ContainsFlat;
use encog\neural\networks\training\BatchSize;
use encog\util\concurrency\DetermineWorkload;
use encog\util\concurrency\MultiThreadable;
use encog\util\EncogValidate;
use encog\util\logging\EncogLogging;
use encog\util\Random;
use Throwable;

/**
 * Implements basic functionality that is needed by each of the propagation
 * methods. The specifics of each of the propagation methods is implemented
 * inside of the PropagationMethod interface implementors.
 */
abstract class Propagation extends BasicTraining implements MLTrain, BatchSize,
		GradientWorkerOwner, MultiThreadable {
	/** @var Random */
	protected $dropoutRandomSource;

	/** @var float */
	private $dropoutRate;

	/** @var FlatNetwork */
	private $currentFlatNetwork;

	/** @var float[] */
	protected $gradients = [];

	/** @var float[] */
	private $lastGradient = [];

	/** @var ContainsFlat */
	protected $network;

	/** @var MLDataSet */
	private $indexable;

	/** @var float */
	private $totalError;

	/** @var bool */
	private $shouldFixFlatSpot;

	/** @var ErrorFunction */
	private $errFunc;

	/** @var int */
	private $batchSize = 0;

	/** @var float */
	private $L1, $L2;

	/** @var bool */
	private $finalized = false;

	/** @var Throwable */
	private $reportedException;

	/** @var GradientWorker[] */
	private $workers;

	/** @var int */
	private $numThreads = 0;

	abstract public function updateWeight(
		array $gradients,
		array &$lastGradient,
		int $index,
		float $dropoutRate = 0.0
	): float;

	public function __construct(ContainsFlat $network, MLDataSet $training) {
		parent::__construct(new TrainingImplementationType(TrainingImplementationType::Iterative));
		$this->dropoutRandomSource = new Random();
		$this->errFunc = new LinearErrorFunction();
		$this->currentFlatNetwork = $network->getFlat();
		$this->network = $network;
		$this->setTraining($training);
		$this->indexable = $training;
		$this->shouldFixFlatSpot = true;
		$this->gradients = array_fill(0, count($this->currentFlatNetwork->getWeights()), 0.0);
		$this->lastGradient = array_fill(0, count($this->currentFlatNetwork->getWeights()), 0.0);
		$this->L1 = 0.0;
		$this->L2 = 0.0;
	}

	public function getDropoutRate(): float {
		return $this->dropoutRate;
	}

	public function setDropoutRate(float $value) {
		$this->dropoutRate = $value;
	}

	public function finishTraining() {
		if (!$this->finalized) {
			if ($this->dropoutRate > 0) {
				$weights = $this->currentFlatNetwork->getWeights();
				foreach ($weights as &$weight) {
					$weight *= 1-$this->dropoutRate;
				}
				$this->currentFlatNetwork->setWeights($weights);
			}
			$this->finalized = true;
		}
		parent::finishTraining();
	}

	public function report(array $gradients, float $error, Throwable $e = null) {
		if ($e === null) {
			foreach ($gradients as $key => $gradient) {
				$this->gradients[$key] += $gradient;
			}
			$this->totalError += $error;
		} else {
			$this->reportedException = $e;
		}
	}

	public function learn() {
		$weights = $this->currentFlatNetwork->getWeights();
		$length = count($this->gradients);
		$dropout = $this->dropoutRate ?? 0.0;
		for ($i = 0; $i < $length; $i++) {
			$weights[$i] += $this->updateWeight(
				$this->gradients,
				$this->lastGradient,
				$i, $dropout
			);
			$this->gradients[$i] = 0;
		}
	}

	public function learnLimited() {
		$weights = $this->currentFlatNetwork->getWeights();
		$length = count($this->gradients);
		$limit = $this->currentFlatNetwork->getConnectionLimit();
		$dropout = $this->dropoutRate ?? 0.0;
		for ($i = 0; $i < $length; $i++) {
			if (abs($weights[$i]) >= $limit) {
				$weights[$i] += $this->updateWeight(
					$this->gradients,
					$this->lastGradient,
					$i, $dropout
				);
			} else {
				$weights[$i] = 0;
			}
			$this->gradients[$i] = 0;
		}
	}

	public function calculateGradients() {
		if (!$this->workers) $this->init();
		if ($this->currentFlatNetwork->getHasContext()) {
			$this->workers[0]->getNetwork()->clearContext();
		}
		$numWorkers = count($this->workers);
		$this->totalError = 0.0;

		if ($numWorkers > 1) {
			// TODO Make GradientWorkers run in separate threads, using "pthreads" extension.
		} else {
			$this->workers[0]->run();
		}

		$this->setError($this->totalError / $numWorkers);
	}

	public function getLastGradient(): array {
		return $this->lastGradient;
	}

	public function setLastGradient(array $values) {
		$this->lastGradient = $values;
	}

	public function getMethod(): MLMethod {
		return $this->network;
	}

	public function getBatchSize(): int {
		return $this->batchSize;
	}

	public function setBatchSize(int $value) {
		$this->batchSize = $value;
	}

	public function getL1(): float {
		return $this->L1;
	}

	public function setL1(float $value) {
		$this->L1 = $value;
	}

	public function getL2(): float {
		return $this->L2;
	}

	public function setL2(float $value) {
		$this->L2 = $value;
	}

	public function getThreadCount(): int {
		return $this->numThreads;
	}

	public function setThreadCount(int $value) {
		$this->numThreads = $value;
	}

	protected function doIteration() {
		try {
			$this->calculateGradients();
			if ($this->currentFlatNetwork->isLimited()) {
				$this->learnLimited();
			} else {
				$this->learn();
			}
			foreach ($this->workers as $worker) {
				$worker->setWeights($this->currentFlatNetwork->getWeights());
			}
			if ($this->currentFlatNetwork->getHasContext()) {
				$this->copyContexts();
			}
		} catch (Throwable $e) {
			EncogValidate::validateNetworkForTraining($this->network, $this->getTraining());
			throw new EncogError("Something went wrong.", 1, $e);
		}
		if ($this->reportedException) {
			throw new EncogError(
				$this->reportedException->getMessage(),
				$this->reportedException->getCode(),
				$this->reportedException
			);
		}
		EncogLogging::log(EncogLogging::LEVEL_INFO,
			"Training iteration done, error: {$this->getError()}");
	}

	protected function initOthers() {}

	private function copyContexts() {
		for ($i = 0, $m = count($this->workers)-1; $i < $m; $i++) {
			$this->workers[$i+1]->getNetwork()->setLayerOutput($this->workers[$i]->getNetwork()->getLayerOutput());
		}
		$this->currentFlatNetwork->setLayerOutput($this->workers[$m]->getNetwork()->getLayerOutput());
	}

	private function init() {
		$flatSpot = array_fill(0, count($this->currentFlatNetwork->getActivationFunctions()), 0.0);
		if ($this->shouldFixFlatSpot) {
			foreach ($this->currentFlatNetwork->getActivationFunctions() as $k => $activation) {
				if ($activation instanceof ActivationSigmoid) {
					$flatSpot[$k] = 0.1;
				}
			}
		}
		$determine = new DetermineWorkload($this->indexable->getRecordCount(), $this->numThreads);
		foreach ($determine->calculateWorkers() as $range) {
			$this->workers[] = new GradientWorker(
				clone $this->currentFlatNetwork,
				$this,
				$this->indexable->openAdditional(),
				$range->getLow(),
				$range->getHigh(),
				$flatSpot,
				$this->errFunc
			);
		}
		$this->initOthers();
	}
}
