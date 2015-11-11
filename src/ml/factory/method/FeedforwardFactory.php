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
namespace encog\ml\factory\method;

use encog\EncogError;
use encog\engine\network\activation\ActivationLinear;
use encog\ml\factory\MLActivationFactory;
use encog\ml\factory\parse\ArchitectureParser;
use encog\ml\MLMethod;
use encog\neural\networks\BasicNetwork;
use encog\neural\networks\layers\BasicLayer;

/**
 * A factory to create feedforward networks.
 */
class FeedforwardFactory {
	public function __construct() {
		$this->factory = new MLActivationFactory();
	}

	public function create(string $architecture, int $input, int $output): MLMethod {
		if ($input < 1) {
			throw new EncogError("Must have at least one input for feedforward.");
		}
		if ($output < 1) {
			throw new EncogError("Must have at least one output for feedforward.");
		}
		$result = new BasicNetwork();
		$layers = ArchitectureParser::parseLayers($architecture);
		$activation = new ActivationLinear();
		$questionPhase = 0;

		foreach ($layers as $layerStr) {
			$defaultCount = $questionPhase == 0 ? $input : $output;
			$layer = ArchitectureParser::parseLayer($layerStr, $defaultCount);

			if ($lookup = $this->factory->create($layer->getName())) {
				$activation = $lookup;
			} else {
				if ($layer->isUsedDefault() && ++$questionPhase > 2) {
					throw new EncogError("Only two ?'s may be used.");
				}
				if ($layer->getCount() == 0) {
					throw new EncogError(
						"Layer can't have zero neurons, Unknown architecture " .
						"element: $architecture, can't parse: {$layer->getName()}"
					);
				}
				$result->addLayer(new BasicLayer($activation,
					$layer->getCount(), $layer->isBias()));
			}
		}
		$result->getStructure()->finalizeStructure();
		$result->reset();
		return $result;
	}

	/** @var MLActivationFactory */
	private $factory;
}
