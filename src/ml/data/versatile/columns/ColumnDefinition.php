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
namespace encog\ml\data\versatile\columns;

use encog\EncogError;
use encog\ml\data\MLDataError;
use encog\ml\data\versatile\NormalizationHelper;

/**
 * Defines a column definition.
 */
class ColumnDefinition {
	/** @var string */
	private $name;
	/** @var ColumnType */
	private $type;
	/** @var float */
	private $low;
	/** @var float */
	private $high;
	/** @var float */
	private $mean;
	/** @var float */
	private $sd;
	/** @var int */
	private $count = 0;
	/** @var int */
	private $index = -1;
	/** @var string[] */
	private $classes = [];
	/** @var NormalizationHelper */
	private $owner;

	public function __construct(string $name, ColumnType $type) {
		$this->low = $this->high = $this->mean = $this->sd = NAN;
		$this->name = $name;
		$this->type = $type;
	}

	public function __toString() {
		return sprintf("[ColumnDefinition:%s(%s);%s]", $this->name, $this->type,
			$this->type == new ColumnType(ColumnType::continuous)
				? sprintf("low=%f,high=%f,mean=%f,sd=%f", $this->low, $this->high, $this->mean, $this->sd)
				: join(",", $this->classes)
		);
	}

	public function analyze(string $value) {
		if ($this->type == new ColumnType(ColumnType::continuous)) {
			if (!$this->owner) {
				throw new MLDataError("Column has no owner.");
			}
			$this->analyzeContinuous($value);
			return;
		}
		if ($this->type == new ColumnType(ColumnType::ordinal)) {
			$this->analyzeOrdinal($value);
			return;
		}
		if ($this->type == new ColumnType(ColumnType::nominal)) {
			$this->analyzeNominal($value);
		}
	}

	public function equals($other): bool {
		if ($other instanceof self) {
			return $this->name == $other->name
				&& $this->type == $other->type
				&& ($this->low == $other->low || (is_nan($this->low) && is_nan($other->low)))
				&& ($this->high == $other->high || (is_nan($this->high) && is_nan($other->high)))
				&& ($this->mean == $other->mean || (is_nan($this->mean) && is_nan($other->mean)))
				&& ($this->sd == $this->sd || (is_nan($this->sd) && is_nan($other->sd)))
				&& $this->count == $other->count
				&& $this->index == $other->index
				&& $this->classes == $other->classes;
		}
		return false;
	}

	public function defineClass(string $name) {
		$this->classes[] = $name;
	}

	public function getName(): string {
		return $this->name;
	}

	public function setName(string $name) {
		$this->name = $name;
	}

	public function getType(): ColumnType {
		return $this->type;
	}

	public function setType(ColumnType $type) {
		$this->type = $type;
	}

	public function getLow(): float {
		return $this->low;
	}

	public function setLow(float $low) {
		$this->low = $low;
	}

	public function getHigh(): float {
		return $this->high;
	}

	public function setHigh(float $high) {
		$this->high = $high;
	}

	public function getMean(): float {
		return $this->mean;
	}

	public function setMean(float $mean) {
		$this->mean = $mean;
	}

	public function getSd(): float {
		return $this->sd;
	}

	public function setSd(float $sd) {
		$this->sd = $sd;
	}

	public function getCount(): int {
		return $this->count;
	}

	public function setCount(int $count) {
		$this->count = $count;
	}

	public function getIndex(): int {
		return $this->index;
	}

	public function setIndex(int $index) {
		$this->index = $index;
	}

	public function getClasses(): array {
		return $this->classes;
	}

	public function setClasses(array $classes) {
		$this->classes = $classes;
	}

	public function getOwner() {
		return $this->owner;
	}

	public function setOwner(NormalizationHelper $owner) {
		$this->owner = $owner;
	}

	private function analyzeNominal(string $value) {
		if (!in_array($value, $this->classes)) {
			$this->classes[] = $value;
		}
	}

	private function analyzeOrdinal(string $value) {
		if (!in_array($value, $this->classes)) {
			throw new EncogError("You must predefine any ordinal values (in order). Undefined ordinal value: $value");
		}
	}

	private function analyzeContinuous(string $value) {
		$d = $this->owner->getFormat()->parse($value);
		if ($this->count < 1) {
			$this->low = $d;
			$this->high = $d;
			$this->mean = $d;
			$this->sd = 0;
			$this->count = 1;
		} else {
			$this->mean += $d;
			$this->low = min($this->low, $d);
			$this->high = max($this->high, $d);
			$this->count++;
		}
	}
}
