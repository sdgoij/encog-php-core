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
namespace encog\ml\data\temporal;

use SplFixedArray;

/**
 * A temporal point is all of the data captured at one point in time to be used
 * for prediction. One or more data items might be captured at this point. The
 * TemporalDataDescription class is used to describe each of these data items
 * captured at each point.
 */
class TemporalPoint {
	/** @var int */
	private $sequence = 0;
	/** @var SplFixedArray */
	private $data;

	public function __construct(int $size) {
		$this->data = SplFixedArray::fromArray(array_fill(0, $size, 0.0));
	}

	public function __toString() {
		return sprintf("[TemporalPoint:Seq:%d,Data:%s]", $this->getSequence(),
			join(",", $this->data->toArray())
		);
	}

	public function compare(TemporalPoint $other): int {
		return $this->getSequence() <=> $other->getSequence();
	}

	public function getData(): SplFixedArray {
		return $this->data;
	}

	public function getDataAt(int $index): float {
		return $this->data[$index];
	}

	public function setData(SplFixedArray $data) {
		return $this->data = $data;
	}

	public function setDataAt(int $index, float $value) {
		$this->data[$index] = $value;
	}

	public function getSequence(): int {
		return $this->sequence;
	}

	public function setSequence(int $sequence) {
		$this->sequence = $sequence;
	}
}
