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

use encog\engine\network\activation\ActivationElliott;
use encog\engine\network\activation\ActivationLinear;
use encog\engine\network\activation\ActivationSigmoid;
use encog\ml\data\basic\BasicMLDataSet;
use encog\neural\networks\BasicNetwork;
use encog\neural\networks\layers\BasicLayer;
use encog\neural\networks\training\propagation\resilient\ResilientPropagation;
use encog\util\data\mnist\MNISTDataSet;
use encog\util\data\mnist\MNISTReader;
use encog\util\Random;

require dirname(__DIR__) . "/vendor/autoload.php";

function main(int $argc, array $argv) {
	if ($argc < 2) {
		echo "Usage: {$argv[0]} <path to extracted MNIST data> [<memory>]\n";
		exit;
	}

	if (!is_dir($argv[1])) {
		echo "{$argv[1]} is not a directory.\n";
		exit;
	}

	$PREFIX = $argv[1];

	$mnistDataSet = new MNISTDataSet(new MNISTReader(
		$PREFIX . "train-labels-idx1-ubyte",
		$PREFIX . "train-images-idx3-ubyte",
		0, 60000
	));

	if ($argv[2] ?? false) {
		$dataset = new BasicMLDataSet();
		$record = 1;
		foreach ($mnistDataSet as $pair) {
			echo "\rReading MNIST training data [$record]     ";
			$dataset->addPair($pair);
			$record++;
		}
		$mnistDataSet->close();
		echo "Done.", PHP_EOL;
	} else {
		$dataset = $mnistDataSet;
	}

	$network = new BasicNetwork();
	$network->addLayer(BasicLayer::create($dataset->getInputSize(), new ActivationLinear(), true));
	$network->addLayer(BasicLayer::create(15, new ActivationElliott(), true));
	$network->addLayer(BasicLayer::create(10, new ActivationSigmoid(), false));
	$network->getStructure()->finalizeStructure();
	$network->reset(42);

	$trainer = new ResilientPropagation($network, $dataset);
	$trainer->setThreadCount(1);

	do {
		echo "Epoch #{$trainer->getIteration()} Error: ";
		$trainer->iteration();
		echo sprintf("%01.16f", $trainer->getError()), PHP_EOL;
	} while ($trainer->getError() > 0.01);

	$test = new MNISTDataSet(new MNISTReader(
		$PREFIX . "t10k-labels-idx1-ubyte",
		$PREFIX . "t10k-images-idx3-ubyte"
	));

	$r = new Random();
	$m = $test->size();

	for ($i = 0; $i < 15; $i++) {
		$pair = $test->get($r->nextInt($m));
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

main($argc, $argv);
