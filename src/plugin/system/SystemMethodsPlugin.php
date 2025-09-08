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

use encog\engine\network\activation\ActivationFunction;
use encog\ml\data\MLDataSet;
use encog\ml\factory\method\FeedforwardFactory;
use encog\ml\factory\MLMethodFactory;
use encog\ml\MLMethod;
use encog\ml\train\MLTrain;
use encog\plugin\EncogPluginBase;
use encog\plugin\EncogPluginError;
use encog\plugin\EncogPluginService1;

/**
 * The system machine learning methods plugin. This provides all of the
 * built in machine learning methods for Encog.
 */
class SystemMethodsPlugin implements EncogPluginService1 {
	public function __construct() {
		$this->factories = [
			MLMethodFactory::TYPE_BAYESIAN    => null,
			MLMethodFactory::TYPE_EPL         => null,
			MLMethodFactory::TYPE_FEEDFORWARD => new FeedforwardFactory(),
			MLMethodFactory::TYPE_NEAT        => null,
			MLMethodFactory::TYPE_PNN         => null,
			MLMethodFactory::TYPE_RBFNETWORK  => null,
			MLMethodFactory::TYPE_SOM         => null,
			MLMethodFactory::TYPE_SVM         => null,
		];
	}

	public function getPluginType(): int {
		return 1;
	}

	public function getPluginServiceType(): int {
		return EncogPluginBase::TYPE_SERVICE;
	}

	public function getPluginName(): string {
		return "HRI-System-Methods";
	}

	public function getPluginDescription(): string {
		return "This plugin provides the built in machine learning methods for Encog.";
	}

	public function createActivationFunction(string $name): ActivationFunction {
		throw new EncogPluginError();
	}

	public function createMethod(string $type, string $arch, int $input, int $output): MLMethod {
		if (!isset($this->factories[$type])) {
			throw new EncogPluginError("Unknown method type: $type");
		}
		return $this->factories[$type]->create($arch, $input, $output);
	}

	public function createTraining(MLMethod $method, MLDataSet $training, string $type, string $args): MLTrain {
		throw new EncogPluginError();
	}

	/** @var array */
	private $factories = [];
}
