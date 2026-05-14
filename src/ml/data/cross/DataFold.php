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
namespace encog\ml\data\cross;

use encog\ml\data\versatile\MatrixMLDataSet;
use encog\ml\MLMethod;

class DataFold {
	/** @var MatrixMLDataSet */
	private $training;
	/** @var MatrixMLDataSet */
	private $validation;
	/** @var MLMethod */
	private $method;
	/** @var float|null */
	private $score;

	public function __construct(MatrixMLDataSet $training, MatrixMLDataSet $validation) {
		$this->training = $training;
		$this->validation = $validation;
	}

	public function getTraining(): MatrixMLDataSet {
		return $this->training;
	}

	public function setTraining(MatrixMLDataSet $training) {
		$this->training = $training;
	}

	public function getValidation(): MatrixMLDataSet {
		return $this->validation;
	}

	public function setValidation(MatrixMLDataSet $validation) {
		$this->validation = $validation;
	}

	public function getMethod() {
		return $this->method;
	}

	public function setMethod(MLMethod $method) {
		$this->method = $method;
	}

	public function getScore(): float {
		return $this->score ?? INF;
	}

	public function setScore(float $score) {
		$this->score = $score;
	}
}
