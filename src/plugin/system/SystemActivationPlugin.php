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
namespace encog\plugin\system;

use encog\EncogError;
use encog\engine\network\activation\ActivationFunction;
use encog\engine\network\activation\ActivationLinear;
use encog\engine\network\activation\ActivationReLU;
use encog\engine\network\activation\ActivationSigmoid;
use encog\engine\network\activation\ActivationSoftMax;
use encog\engine\network\activation\ActivationSteepenedSigmoid;
use encog\engine\network\activation\ActivationTANH;
use encog\ml\data\MLDataSet;
use encog\ml\factory\MLActivationFactory;
use encog\ml\MLMethod;
use encog\ml\train\MLTrain;
use encog\plugin\EncogPluginBase;
use encog\plugin\EncogPluginError;
use encog\plugin\EncogPluginService1;
use encog\util\csv\CSVFormat;
use encog\util\csv\NumberList;

/**
 * This plugin provides the built in activation functions for Encog.
 */
class SystemActivationPlugin implements EncogPluginService1 {
	public function __construct() {
		$this->classes = [
			MLActivationFactory::BIPOLAR     => null,
			MLActivationFactory::COMPETITIVE => null,
			MLActivationFactory::GAUSSIAN    => null,
			MLActivationFactory::LINEAR      => ActivationLinear::class,
			MLActivationFactory::LOG         => null,
			MLActivationFactory::RAMP        => null,
			MLActivationFactory::RELU        => ActivationReLU::class,
			MLActivationFactory::SIGMOID     => ActivationSigmoid::class,
			MLActivationFactory::SIN         => null,
			MLActivationFactory::SOFTMAX     => ActivationSoftMax::class,
			MLActivationFactory::SSIGMOID    => ActivationSteepenedSigmoid::class,
			MLActivationFactory::STEP        => null,
			MLActivationFactory::TANH        => ActivationTANH::class,
		];
	}

	public function getPluginType(): int {
		return 1;
	}

	public function getPluginServiceType(): int {
		return EncogPluginBase::TYPE_SERVICE;
	}

	public function getPluginName(): string {
		return "HRI-System-Activation";
	}

	public function getPluginDescription(): string {
		return "This plugin provides the built in activation functions for Encog.";
	}

	public function createActivationFunction(string $fn): ActivationFunction {
		if (($startIndex = strpos($fn, "[")) !== false) {
			if (($endIndex = strpos($fn, "]")) === false) {
				throw new EncogError("Unbounded '[' while parsing activation function.");
			}
			$name = strtolower(substr($fn, 0, $startIndex));
			$params = NumberList::fromList(CSVFormat::EgFormat(), substr($fn, $startIndex+1, $endIndex));
		} else {
			$name = strtolower($fn);
			$params = [];
		}
		$activation = $this->create($name);

		if (count($params)) {
			$expect = count($activation->getParamNames());
			if ($expect != count($params)) {
				throw new EncogError("$name expected $expect params, but " . count($params) . " were provided");
			}
			for ($i = 0; $i < $expect; $i++) {
				$activation->setParam($i, $params[$i]);
			}
		}
		return $activation;
	}

	private function create(string $name): ActivationFunction {
		if (!isset($this->classes[$name])) {
			throw new EncogPluginError();
		}
		return new $this->classes[$name];
	}

	public function createMethod(string $type, string $arch, int $input, int $output): MLMethod {
		throw new EncogPluginError();
	}

	public function createTraining(MLMethod $method, MLDataSet $training, string $type, string $args): MLTrain {
		throw new EncogPluginError();
	}

	/** @var array<string, string|null> */
	private $classes = [];
}
