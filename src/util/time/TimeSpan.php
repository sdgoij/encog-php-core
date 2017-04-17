<?php
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
namespace encog\util\time;

use DateInterval;
use DatePeriod;
use DateTimeInterface;

/**
 * A time span between two Dates.
 */
class TimeSpan {
	const YEARS_CENTURY  = 100;
	const HOURS_DAY      = 24;
	const MINUTES_HOUR   = 60;
	const SECONDS_MINUTE = 60;
	const MONTHS_YEAR    = 12;
	const DAYS_WEEK      = 7;
	const YEARS_MIL      = 1000;
	const YEARS_SCORE    = 20;

	/** @var DateTimeInterface */
	private $from, $to;

	/** @var DateInterval */
	private $month;

	public function __construct(DateTimeInterface $from, DateTimeInterface $to) {
		$this->month = new DateInterval("P1M");
		$this->from = $from;
		$this->to = $to;
	}

	public function getFrom(): DateTimeInterface {
		return $this->from;
	}

	public function getTo(): DateTimeInterface {
		return $this->to;
	}

	public function getSpan(TimeUnit $unit): int {
		switch ($unit) {
			case new TimeUnit(TimeUnit::SECONDS):
				return $this->getSpanSeconds();
			case new TimeUnit(TimeUnit::MINUTES):
				return $this->getSpanMinutes();
			case new TimeUnit(TimeUnit::HOURS):
				return $this->getSpanHours();
			case new TimeUnit(TimeUnit::DAYS):
				return $this->getSpanDays();
			case new TimeUnit(TimeUnit::FORTNIGHTS):
				return $this->getSpanFortnights();
			case new TimeUnit(TimeUnit::WEEKS):
				return $this->getSpanWeeks();
			case new TimeUnit(TimeUnit::MONTHS):
				return $this->getSpanMonths();
			case new TimeUnit(TimeUnit::YEARS):
				return $this->getSpanYears();
			case new TimeUnit(TimeUnit::SCORES):
				return $this->getSpanScores();
			case new TimeUnit(TimeUnit::CENTURIES):
				return $this->getSpanCenturies();
			case new TimeUnit(TimeUnit::MILLENNIA):
				return $this->getSpanMillennia();
			default:
				return 0;
		}
	}

	private function getSpanCenturies(): int {
		return (int)($this->getSpanYears() / self::YEARS_CENTURY);
	}

	private function getSpanDays(): int {
		return (int)($this->getSpanHours() / self::HOURS_DAY);
	}

	private function getSpanFortnights(): int {
		return (int)($this->getSpanWeeks() / 2);
	}

	private function getSpanHours(): int {
		return (int)($this->getSpanMinutes() / self::MINUTES_HOUR);
	}

	private function getSpanMillennia(): int {
		return (int)($this->getSpanYears() / self::YEARS_MIL);
	}

	private function getSpanMinutes(): int {
		return (int)($this->getSpanSeconds() / self::SECONDS_MINUTE);
	}

	private function getSpanMonths(): int {
		return iterator_count(new DatePeriod($this->from, $this->month, $this->to));
	}

	private function getSpanScores(): int {
		return (int)($this->getSpanYears() / self::YEARS_SCORE);
	}

	private function getSpanSeconds(): int {
		return $this->to->getTimestamp() - $this->from->getTimestamp();
	}

	private function getSpanWeeks(): int {
		return (int)($this->getSpanDays() / self::DAYS_WEEK);
	}

	private function getSpanYears(): int {
		return (int)($this->getSpanMonths() / self::MONTHS_YEAR);
	}
}
