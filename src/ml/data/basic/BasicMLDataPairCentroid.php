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
namespace encog\ml\data\basic;

use InvalidArgumentException;
use encog\util\kmeans\Centroid;

/**
 * A centroid for BasicMLDataPair.
 */
class BasicMLDataPairCentroid implements Centroid {
	public function __construct(BasicMLDataPair $pair) {
		$this->value = $pair->getInput()->clone();
	}

	public function add($element) {
		if (!$element instanceof BasicMLDataPair) {
			throw new InvalidArgumentException();
		}
		$add = $element->getInputArray();
		$size = $this->value->size();
		foreach ($this->value->getData() as $k => $v) {
			$this->value->setDataAt($k, (($v * $size) + $add[$k]) / ($size + 1));
		}
	}

	public function remove($element) {
		if (!$element instanceof BasicMLDataPair) {
			throw new InvalidArgumentException();
		}
		$rm = $element->getInputArray();
		$size = $this->value->size();
		foreach ($this->value->getData() as $k => $v) {
			$this->value->setDataAt($k, (($v * $size) - $rm[$k]) / ($size - 1));
		}
	}

	public function distance($element): float {
		if (!$element instanceof BasicMLDataPair) {
			throw new InvalidArgumentException();
		}
		$diff = $this->value->minus($element->getInput());
		$sum = 0.0;

		foreach ($diff->getData() as $value) {
			$sum += $value * $value;
		}
		return sqrt($sum);
	}

	/** @var BasicMLData */
	private $value;
}
