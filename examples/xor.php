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

use encog\ml\data\basic\BasicMLData;
use encog\neural\networks\training\propagation\resilient\ResilientPropagation;
use encog\test\neural\networks\XORUtil;
use encog\util\Random;

require dirname(__DIR__) . "/vendor/autoload.php";

$dataset = XORUtil::createDataSet();
$network = XORUtil::createUnTrainedNetwork();
$network->getFlat()->randomize();

$trainer = new ResilientPropagation($network, $dataset);
$random = new Random();

do {
	echo "Epoch #{$trainer->getIteration()} Error: ";
	$trainer->iteration();
	echo sprintf("%01.16f", $trainer->getError()), PHP_EOL;
} while ($trainer->getError() > 0.00001);

for ($i = 0; $i < 25; $i++) {
	$a = $random->nextInt(2);
	$b = $random->nextInt(2);

	echo "$a ^ $b => ", sprintf("%01.5f",
		$network->compute(new BasicMLData([$a, $b]))->getDataAt(0)
	), PHP_EOL;
}
