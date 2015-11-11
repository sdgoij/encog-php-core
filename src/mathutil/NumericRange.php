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
namespace encog\mathutil;

class NumericRange {
	const FIVE = 5;

	private $high, $low;
	private $mean, $rms, $sd;
	private $samples;

	public function __construct(array $values) {
		$total = $rms = $dev = 0;
		foreach ($values as $value) {
			$this->high = max($this->high, $value);
			$this->low = min($this->low ?? 0.0, $value);
			$total += $value;
			$rms += $value * $value;
		}
		$this->samples = count($values);
		$this->mean = $total / $this->samples;
		$this->rms = sqrt($rms / $this->samples);
		foreach ($values as $value) {
			$dev += pow($value-$this->mean, 2);
		}
		$this->sd = sqrt($dev/$this->samples);
	}

	public function __toString() {
		return sprintf("Range: %01.5f to %01.5f,samples: %d,mean: %01.5f,rms: %01.5f,s.deviation: %01.5f",
			$this->low,
			$this->high,
			$this->samples,
			$this->mean,
			$this->rms,
			$this->sd
		);
	}

	public function getHigh(): float {
		return $this->high;
	}

	public function getLow(): float {
		return $this->low;
	}

	public function getMean(): float {
		return $this->mean;
	}

	public function getRms(): float {
		return $this->rms;
	}

	public function getSd(): float {
		return $this->sd;
	}

	public function getSamples(): int {
		return $this->samples;
	}
}
