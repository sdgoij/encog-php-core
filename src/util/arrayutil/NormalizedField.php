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
namespace encog\util\arrayutil;

use encog\EncogError;
use encog\mathutil\Equilateral;

require_once __DIR__ . "/NormalizationAction.php";

/**
 * This object holds the normalization stats for a column. This includes the
 * actual and desired high-low range for this column.
 */
class NormalizedField {
	/** @var float */
	private $actualHigh;
	/** @var float */
	private $actualLow;
	/** @var float */
	private $normalizedHigh;
	/** @var float */
	private $normalizedLow;
	/** @var NormalizationAction */
	private $action;
	/** @var string */
	private $name;
	/** @var ClassItem[] */
	private $classes = [];
	/** @var Equilateral */
	private $eq;
	/** @var array */
	private $lookup = [];

	public static function createNamedAction(string $name, NormalizationAction $action,
			float $ah = .0, float $al = .0, float $nh = .0, float $nl = .0): NormalizedField {
		$field = new static($nh, $nl, $ah, $al);
		$field->action = $action;
		$field->name = $name;
		return $field;
	}

	public function __construct(float $nh, float $nl, float $ah = -INF, float $al = INF) {
		$this->action = new NormalizationAction(NormalizationAction::Normalize);
		$this->actualHigh = $ah;
		$this->actualLow = $al;
		$this->normalizedHigh = $nh;
		$this->normalizedLow = $nl;
	}

	public function __toString() {
		return sprintf("[NormalizedField name=%s, actualHigh=%f, actualLow=%f]",
			$this->name, $this->actualHigh, $this->actualLow);
	}

	public function analyze(float $value) {
		$this->actualHigh = max($this->actualHigh, $value);
		$this->actualLow = min($this->actualLow, $value);
	}

	public final function init() {
		if ($this->action == new NormalizationAction(NormalizationAction::Equilateral)) {
			if (count($this->classes) < Equilateral::MIN_EQ) {
				throw new EncogError("There must be at least three classes to make use of equilateral normalization.");
			}
			$this->eq = new Equilateral(count($this->classes),
				$this->normalizedHigh, $this->normalizedLow);
		}
		foreach ($this->classes as $class) {
			$this->lookup[$class->getName()] = $class->getIndex();
		}
	}

	public final function denormalize(float $value): float {
		return (($this->actualLow - $this->actualHigh) * $value
			- $this->normalizedHigh * $this->actualLow + $this->actualHigh
			* $this->normalizedLow) / ($this->normalizedLow - $this->normalizedHigh);
	}

	public final function normalize(float $value): float {
		if ($value > $this->actualHigh) {
			return $this->normalizedHigh;
		}
		if ($value < $this->actualLow) {
			return $this->normalizedLow;
		}
		return (($value - $this->actualLow) / ($this->actualHigh - $this->actualLow))
			* ($this->normalizedHigh - $this->normalizedLow) + $this->normalizedLow;
	}

	public function isClassify(): bool {
		return $this->action->isClassify();
	}

	public function lookup(string $key): int {
		return $this->lookup[$key] ?? -1;
	}

	public function getActualHigh(): float {
		return $this->actualHigh;
	}

	public function setActualHigh(float $actualHigh) {
		$this->actualHigh = $actualHigh;
	}

	public function getActualLow(): float {
		return $this->actualLow;
	}

	public function setActualLow(float $actualLow) {
		$this->actualLow = $actualLow;
	}

	public function getNormalizedHigh(): float {
		return $this->normalizedHigh;
	}

	public function setNormalizedHigh(float $normalizedHigh) {
		$this->normalizedHigh = $normalizedHigh;
	}

	public function getNormalizedLow(): float {
		return $this->normalizedLow;
	}

	public function setNormalizedLow(float $normalizedLow) {
		$this->normalizedLow = $normalizedLow;
	}

	public function getAction(): NormalizationAction {
		return $this->action;
	}

	public function setAction(NormalizationAction $action) {
		$this->action = $action;
	}

	public function getName(): string {
		return $this->name;
	}

	public function setName(string $name) {
		$this->name = $name;
	}

	public function getClasses(): array {
		return $this->classes;
	}

	public function setClasses(array $classes) {
		$this->classes = $classes;
	}

	public function getEq(): Equilateral {
		return $this->eq;
	}

	public function setEq(Equilateral $eq) {
		$this->eq = $eq;
	}
}
