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
namespace encog\neural\pattern;

use encog\engine\network\activation\ActivationFunction;

/**
 * Abstract base class for Elman and Jordan Patterns.
 */
abstract class AbstractRecurrentPattern implements NetworkPattern {
	public function __construct() {
		$this->input = -1;
		$this->output = -1;
		$this->hidden = -1;
	}

	public function clear() {
		$this->hidden = -1;
	}

	public function setActivationFunction(ActivationFunction $activation) {
		$this->activation = $activation;
	}

	public function setInputNeurons(int $count) {
		$this->input = $count;
	}

	public function setOutputNeurons(int $count) {
		$this->output = $count;
	}

	/** @var int */
	protected $input, $output, $hidden;
	/** @var ActivationFunction */
	protected $activation;
}
