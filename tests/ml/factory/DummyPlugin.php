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
namespace encog\test\ml\factory;

use encog\engine\network\activation\ActivationFunction;
use encog\engine\network\activation\ActivationLinear;
use encog\ml\data\basic\BasicMLData;
use encog\ml\data\MLDataSet;
use encog\ml\MLMethod;
use encog\ml\train\MLTrain;
use encog\ml\train\strategy\Strategy;
use encog\ml\TrainingImplementationType;
use encog\neural\networks\training\propagation\TrainingContinuation;
use encog\plugin\EncogPluginBase;
use encog\plugin\EncogPluginError;
use encog\plugin\EncogPluginService1;

class DummyPlugin implements EncogPluginService1 {
	public function getPluginType(): int {
		return 1;
	}

	public function getPluginServiceType(): int {
		return EncogPluginBase::TYPE_SERVICE;
	}

	public function getPluginName(): string {
		return "encog.test.ml.factory.dummy";
	}

	public function getPluginDescription(): string {
		return "Used for testing Method and Activation Factories.";
	}

	public function createActivationFunction(string $name): ActivationFunction {
		if ($name == "TEST_FAIL") {
			throw new EncogPluginError("Cannot create activation function '$name'");
		}
		return new ActivationLinear();
	}

	public function createMethod(string $type, string $arch, int $input, int $output): MLMethod {
		if ($type == "TEST_FAIL" || substr($type, 0, 5) != "TEST_") {
			throw new EncogPluginError("Cannot create method type '$type'");
		}
		return new class implements MLMethod {};
	}

	public function createTraining(MLMethod $method, MLDataSet $training, string $type, string $args): MLTrain {
		if ($type == "TEST_FAIL" || substr($type, 0, 5) != "TEST_") {
			throw new EncogPluginError("Cannot create training type '$type'");
		}
		return new class implements MLTrain {
			public function getTrainingImplementationType(): TrainingImplementationType {
				return new TrainingImplementationType();
			}
			public function isTrainingDone(): bool { return true; }
			public function getTraining(): MLDataSet { return new BasicMLData([]); }
			public function iteration(int $count = 0) {}
			public function getError(): float { return 1.0; }
			public function finishTraining() {}
			public function getIteration(): int { return 1; }
			public function canContinue(): bool { return false; }
			public function pause(): TrainingContinuation { return new TrainingContinuation(); }
			public function resume(TrainingContinuation $state) {}
			public function addStrategy(Strategy $strategy) {}
			public function getMethod(): MLMethod { return new class implements MLMethod {}; }
			public function getStrategies(): array { return []; }
			public function setError(float $error) {}
			public function setIteration(int $iteration) {}
		};
	}
}
