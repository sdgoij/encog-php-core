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
namespace encog\neural\networks\training\propagation;

use Doctrine\Instantiator\Exception\InvalidArgumentException;

class TrainingContinuation {
	public function getContents(): array {
		return $this->contents;
	}
	public function get(string $name) {
		if (!isset($this->contents[$name])) {
			throw new InvalidArgumentException("Object '$name' not found.");
		}
		return $this->contents[$name];
	}
	public function put(string $index, array $list) {
		$this->contents[$index] = $list;
	}
	public function set(string $index, $object) {
		$this->contents[$index] = $object;
	}
	public function getTrainingType(): string {
		return $this->type;
	}
	public function setTrainingType(string $type) {
		$this->type = $type;
	}
	private $contents;
	private $type;
}
