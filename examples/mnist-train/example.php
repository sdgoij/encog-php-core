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

use encog\engine\network\activation\ActivationElliott;
use encog\engine\network\activation\ActivationLinear;
use encog\engine\network\activation\ActivationSoftMax;
use encog\ml\train\strategy\StopTraining;
use encog\neural\networks\BasicNetwork;
use encog\neural\networks\layers\BasicLayer;
use encog\neural\networks\training\propagation\resilient\ResilientPropagation;
use encog\util\data\mnist\MNISTDataSet;
use encog\util\data\mnist\MNISTReader;

require __DIR__ . "/../../vendor/autoload.php";

function main() {
	$data_dir = prepare_mnist_data();

	$dataset = new MNISTDataSet(new MNISTReader(
		$data_dir . "train-labels-idx1-ubyte.gz",
		$data_dir . "train-images-idx3-ubyte.gz",
		// Only train on first 6000 records, for the purpose of this example.
		0, 6000
	));

	$network = new BasicNetwork();
	$network->addLayer(BasicLayer::create($dataset->getInputSize(), new ActivationLinear(), true));
	$network->addLayer(BasicLayer::create(38, new ActivationElliott(), true));
	$network->addLayer(BasicLayer::create(10, new ActivationSoftMax(), false));
	$network->getStructure()->finalizeStructure();
	// Use seed "42" to initialise the network, so we get reproducible results.
	$network->reset(42);

	$stop = new StopTraining(StopTraining::DEFAULT_MIN_IMPROVEMENT, 3);
	$trainer = new ResilientPropagation($network, $dataset);
	$trainer->setThreadCount(1);
	$trainer->addStrategy($stop);

	do {
		echo "Epoch #{$trainer->getIteration()} Error: ";
		$trainer->iteration();
		echo sprintf("%01.16f", $trainer->getError()), PHP_EOL;
	} while (!$stop->shouldStop());

	$test = new MNISTDataSet(new MNISTReader(
		$data_dir . "t10k-labels-idx1-ubyte.gz",
		$data_dir . "t10k-images-idx3-ubyte.gz"
	));

	foreach ($test as $pair) {
		$result = $network->compute($pair->getInput());
		echo "IDEAL:  ", join(", ", ff($pair->getIdealArray())), PHP_EOL;
		echo "OUTPUT: ", join(", ", ff($result->getData()->toArray())), PHP_EOL;
		echo str_repeat("-", 66), PHP_EOL;
	}
}

function ff(array $values): array {
	foreach ($values as $value) {
		$result[] = sprintf("%1.02f", $value);
	}
	return $result ?? [];
}

function prepare_mnist_data(): string {
	$temp_dir = sys_get_temp_dir() . "/encog-php-core/examples/mnist/";
	$base_url = "http://yann.lecun.com/exdb/mnist/";
	if (!is_dir($temp_dir)) {
		mkdir($temp_dir, 0777, true);
	}

	if (!file_exists($temp_dir . "train-labels-idx1-ubyte.gz")) {
		echo "Downloading 'train-labels-idx1-ubyte.gz' ... ";
		file_put_contents($temp_dir . "train-labels-idx1-ubyte.gz",
			file_get_contents($base_url . "train-labels-idx1-ubyte.gz"));
		echo "DONE!\n";
	}
	if (!file_exists($temp_dir . "train-images-idx3-ubyte.gz")) {
		echo "Downloading 'train-images-idx3-ubyte.gz' ... ";
		file_put_contents($temp_dir . "train-images-idx3-ubyte.gz",
			file_get_contents($base_url . "train-images-idx3-ubyte.gz"));
		echo "DONE!\n";
	}
	if (!file_exists($temp_dir . "t10k-labels-idx1-ubyte.gz")) {
		echo "Downloading 't10k-labels-idx1-ubyte.gz' ... ";
		file_put_contents($temp_dir . "t10k-labels-idx1-ubyte.gz",
			file_get_contents($base_url . "t10k-labels-idx1-ubyte.gz"));
		echo "DONE!\n";
	}
	if (!file_exists($temp_dir . "t10k-images-idx3-ubyte.gz")) {
		echo "Downloading 't10k-images-idx3-ubyte.gz' ... ";
		file_put_contents($temp_dir . "t10k-images-idx3-ubyte.gz",
			file_get_contents($base_url . "t10k-images-idx3-ubyte.gz"));
		echo "DONE!\n";
	}

	return $temp_dir;
}

// Wrapped in "main" function, so HHVM can JIT the code.
main();
