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

use DateTimeInterface;
use encog\ml\data\basic\BasicMLData;
use encog\ml\data\basic\BasicMLDataPair;
use encog\ml\data\basic\BasicMLDataSet;
use encog\ml\data\MLData;
use encog\ml\data\MLDataPair;
use encog\util\time\TimeSpan;
use encog\util\time\TimeUnit;
use SplFixedArray;

/**
 * This class implements a temporal neural data set. A temporal neural dataset
 * is designed to use a neural network to predict.
 *
 * A temporal dataset is a stream of data over a time range. This time range is
 * broken up into "points". Each point can contain one or more values. These
 * values are either the values that you would like to predict, or use to
 * predict. It is possible for a value to be both predicted and used to predict.
 * For example, if you were trying to predict a trend in a stock's price
 * fluctuations you might very well use the security price for both.
 *
 * Each point that we have data for is stored in the TemporalPoint class. Each
 * TemporalPoint will contain one more data values. These data values are
 * described by the TemporalDataDescription class. For example, if you had five
 * TemporalDataDescription objects added to this class, each Temporal point
 * object would contain five values.
 *
 * Points are arranged by sequence number. No two points can have the same
 * sequence numbers. Methods are provided to allow you to add points using the
 * DateTime interface. These dates are resolved to sequence number using the level of
 * granularity specified for this class. No two points can occupy the same
 * granularity increment.
 */
class TemporalMLDataSet extends BasicMLDataSet {
	/** @var TemporalDataDescription[] */
	private $descriptions = [];
	/** @var TemporalPoint[] */
	private $points = [];
	/** @var int */
	private $inputWindowSize;
	/** @var int */
	private $predictWindowSize;
	/** @var int */
	private $lowSequence;
	/** @var int */
	private $highSequence;
	/** @var int */
	private $desiredSetSize;
	/** @var int */
	private $inputNeuronCount = 0;
	/** @var int */
	private $outputNeuronCount = 0;
	/** @var DateTimeInterface */
	private $startingPoint;
	/** @var TimeUnit */
	private $sequenceGranularity;

	public function __construct(int $inputWindowSize, int $predictWindowSize) {
		parent::__construct([], null);
		$this->inputWindowSize = $inputWindowSize;
		$this->predictWindowSize = $predictWindowSize;
		$this->lowSequence = PHP_INT_MIN;
		$this->highSequence = PHP_INT_MAX;
		$this->desiredSetSize = PHP_INT_MAX;
		$this->sequenceGranularity = new TimeUnit(TimeUnit::DAYS);
		$this->startingPoint = null;
	}

	public function add(MLData $input, MLData $ideal = null) {
		throw new TemporalError("Direct adds to the temporal dataset are not supported. "
			. "Add TemporalPoint objects and call generate.");
	}

	public function addPair(MLDataPair $pair) {
		throw new TemporalError("Direct adds to the temporal dataset are not supported. "
			. "Add TemporalPoint objects and call generate.");
	}

	public function addDescription(TemporalDataDescription $desc) {
		if ($this->points) {
			throw new TemporalError("Can't add anymore descriptions, there are already temporal points defined.");
		}
		$desc->setIndex(count($this->descriptions));
		$this->descriptions[] = $desc;
		$this->calculateNeuronCounts();
	}

	public function calculateActualSetSize(): int {
		return min($this->desiredSetSize, $this->calculatePointsInRange());
	}

	public function calculateNeuronCounts() {
		$this->inputNeuronCount = 0;
		$this->outputNeuronCount = 0;

		foreach ($this->descriptions as $desc) {
			if ($desc->isInput()) {
				$this->inputNeuronCount += $this->inputWindowSize;
			}
			if ($desc->isPredict()) {
				$this->outputNeuronCount += $this->predictWindowSize;
			}
		}
	}

	public function calculatePointsInRange(): int {
		$result = 0;
		foreach ($this->points as $point) {
			if ($this->isPointInRange($point)) {
				$result++;
			}
		}
		return $result;
	}

	public function calculateStartIndex(): int {
		foreach ($this->points as $key => $point) {
			if ($this->isPointInRange($point)) {
				return $key;
			}
		}
		return -1;
	}

	public function clear() {
		$this->descriptions = [];
		$this->points = [];
		$this->setData([]);
	}

	public function getSequenceFromDate(DateTimeInterface $when): int {
		if ($this->startingPoint) {
			$span = new TimeSpan($this->startingPoint, $when);
			return $span->getSpan($this->sequenceGranularity);
		}
		$this->startingPoint = $when;
		return 0;
	}

	public function createPoint(int $sequence): TemporalPoint {
		$point = new TemporalPoint(count($this->descriptions));
		$point->setSequence($sequence);
		$this->points[] = $point;
		return $point;
	}

	public function createPointDate(DateTimeInterface $when): TemporalPoint {
		return $this->createPoint($this->getSequenceFromDate($when));
	}

	public function generate() {
		$this->sortPoints();
		$start = $this->calculateStartIndex()+1;
		$range = $start+$this->calculateActualSetSize()
			- $this->predictWindowSize
			- $this->inputWindowSize;
		for ($i = $start; $i < $range; $i++) {
			parent::addPair(new BasicMLDataPair(
				$this->generateInputData($i),
				$this->generateOutputData($i+$this->inputWindowSize)
			));
		}
	}

	public function generateInputData(int $index): BasicMLData {
		$result = new BasicMLData($this->inputNeuronCount);
		for ($i = 0, $ri = 0; $i < $this->inputWindowSize; $i++) {
			foreach ($this->descriptions as $desc) {
				if ($desc->isInput()) {
					$result->setDataAt($ri++, $this->formatData($desc, $index+$i));
				}
			}
		}
		return $result;
	}

	public function generateOutputData(int $index): BasicMLData {
		if ($index+$this->predictWindowSize > count($this->points)) {
			throw new TemporalError("Can't generate prediction temporal data beyond the end of provided data.");
		}
		$result = new BasicMLData($this->outputNeuronCount);
		for ($i = 0; $i < $this->predictWindowSize; $i++) {
			foreach ($this->descriptions as $desc) {
				if ($desc->isPredict()) {
					$result->setDataAt($i, $this->formatData($desc, $index+$i));
				}
			}
		}
		return $result;
	}

	public function isPointInRange(TemporalPoint $point): bool {
		return $point->getSequence() >= $this->getLowSequence()
				&& $point->getSequence() <= $this->getHighSequence();
	}

	public function getDescriptions(): array {
		return $this->descriptions;
	}

	public function getDesiredSetSize(): int {
		return $this->desiredSetSize;
	}

	public function setDesiredSetSize(int $size) {
		$this->desiredSetSize = $size;
	}

	public function getInputNeuronCount(): int {
		return $this->inputNeuronCount;
	}

	public function getOutputNeuronCount(): int {
		return $this->outputNeuronCount;
	}

	public function getInputWindowSize(): int {
		return $this->inputWindowSize;
	}

	public function setInputWindowSize(int $size) {
		$this->inputWindowSize = $size;
	}

	public function getPredictWindowSize(): int {
		return $this->predictWindowSize;
	}

	public function setPredictWindowSize(int $size) {
		$this->predictWindowSize = $size;
	}

	public function getLowSequence(): float {
		return $this->lowSequence;
	}

	public function setLowSequence(int $low) {
		$this->lowSequence = $low;
	}

	public function getHighSequence(): float {
		return $this->highSequence;
	}

	public function setHighSequence(int $high) {
		$this->highSequence = $high;
	}

	public function getStartingPoint() {
		return $this->startingPoint;
	}

	public function setStartingPoint(DateTimeInterface $date) {
		$this->startingPoint = $date;
	}

	public function getSequenceGranularity(): TimeUnit {
		return $this->sequenceGranularity;
	}

	public function setSequenceGranularity(TimeUnit $unit) {
		$this->sequenceGranularity = $unit;
	}

	public function &getPoints(): array {
		return $this->points;
	}

	public function sortPoints() {
		usort($this->points, function(TemporalPoint $a, TemporalPoint $b) {
			return $a->compare($b);
		});
	}

	private function formatData(TemporalDataDescription $desc, int $index): float {
		$result = new SplFixedArray(1);
		switch ($desc->getType()) {
			case TemporalDataType::$DELTA_CHANGE:
				$result[0] = $this->getDataDeltaChange($desc, $index);
				break;
			case TemporalDataType::$PERCENT_CHANGE:
				$result[0] = $this->getDataPercentChange($desc, $index);
				break;
			case TemporalDataType::$RAW:
				$result[0] = $this->getRAW($desc, $index);
				break;
			default:
				throw new TemporalError("Unsupported data type.");
		}
		if ($activation = $desc->getActivation()) {
			$activation->activationFunction($result, 0, 1);
		}
		return $result[0];
	}

	private function getDataDeltaChange(TemporalDataDescription $desc, int $index): float {
		if ($index != 0) {
			$cv = $this->points[$index]->getDataAt($desc->getIndex());
			$pv = $this->points[$index-1]->getDataAt($desc->getIndex());
			return $cv - $pv;
		}
		return 0.0;
	}

	private function getDataPercentChange(TemporalDataDescription $desc, int $index): float {
		if ($index != 0) {
			$cv = $this->points[$index]->getDataAt($desc->getIndex());
			$pv = $this->points[$index-1]->getDataAt($desc->getIndex());
			return ($cv - $pv) / $pv;
		}
		return 0.0;
	}

	private function getRAW(TemporalDataDescription $desc, int $index): float {
		return $this->points[$index-1]->getDataAt($desc->getIndex());
	}
}
