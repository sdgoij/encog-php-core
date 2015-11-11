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
namespace encog\ml\data\buffer\codec;

use encog\ml\data\basic\BasicMLDataPair;
use encog\ml\data\MLDataPair;
use encog\ml\data\MLDataSet;
use Iterator;

/**
 * A CODEC that works with the MLDataSet class.
 */
class MLDataSetCODEC implements DataSetCODEC {
	/** @var MLDataSet */
	private $data;
	/** @var Iterator */
	private $iterator;
	private $input;
	private $ideal;

	public function __construct(MLDataSet $data) {
		$this->input = $data->getInputSize();
		$this->ideal = $data->getIdealSize();
		$this->data = $data;
	}

	public function read(array &$input, array &$ideal, float &$significance): bool {
		if ($this->iterator && $this->iterator->valid()) {
			/** @var MLDataPair $pair */
			$pair = $this->iterator->current();
			$significance = $pair->getSignificance();
			$input = $pair->getInputArray();
			$ideal = $pair->getIdealArray();
			$this->iterator->next();
			return $this->iterator->valid();
		}
		return false;
	}

	public function write(array $input, array $ideal, float $significance) {
		$pair = BasicMLDataPair::createPair($this->input, $this->ideal);
		$pair->setSignificance($significance);
		$pair->setInputArray($input);
		$pair->setIdealArray($ideal);
		$this->data->addPair($pair);
	}

	public function prepareWrite(int $records, int $input, int $ideal) {
		$this->input = $input;
		$this->ideal = $ideal;
	}

	public function prepareRead() {
		$this->iterator = $this->data->getIterator();
	}


	public function getInputSize(): int {
		return $this->input;
	}

	public function getIdealSize(): int {
		return $this->ideal;
	}

	public function close() {}
}
