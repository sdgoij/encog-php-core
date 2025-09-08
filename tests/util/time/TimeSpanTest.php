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
namespace encog\test\util\time;

use DateTime;
use encog\util\time\TimeSpan;
use encog\util\time\TimeUnit;
use PHPUnit\Framework\TestCase;

class TimeSpanTest extends TestCase {
	public function testSpanSeconds() {
		$this->performSpanTest(new TimeUnit(TimeUnit::SECONDS), [
			[new DateTime("20101010000000"), new DateTime("20101010000000"), 0],
			[new DateTime("20190102000000"), new DateTime("20190102000042"), 42],
			[new DateTime("20101010000000"), new DateTime("20101010000201"), 121],
			[new DateTime("20201212133700"), new DateTime("20201212135917"), 1337],
			[new DateTime("20190102000000"), new DateTime("20190102001515"), 915],
			[new DateTime("20301212123700"), new DateTime("20301212123759"), 59],
			[new DateTime("20101010000000"), new DateTime("20101010000001"), 1],
		]);
	}

	public function testGetSpanMinutes() {
		$this->performSpanTest(new TimeUnit(TimeUnit::MINUTES), [
			[new DateTime("20101010000000"), new DateTime("20101010000000"), 0],
			[new DateTime("20190102000000"), new DateTime("20190102004200"), 42],
			[new DateTime("20101010000000"), new DateTime("20101010020100"), 121],
			[new DateTime("20301212120100"), new DateTime("20301212130000"), 59],
			[new DateTime("20190102000000"), new DateTime("20190102001515"), 15],
			[new DateTime("20101010000000"), new DateTime("20101010000100"), 1],
		]);
	}

	public function testSpanHours() {
		$this->performSpanTest(new TimeUnit(TimeUnit::HOURS), [
			[new DateTime("20190102000000"), new DateTime("20190102000000"), 0],
			[new DateTime("20190102000000"), new DateTime("20190103000000"), 24],
			[new DateTime("20190102000000"), new DateTime("20190107070000"), 127],
			[new DateTime("20190102000000"), new DateTime("20190102020202"), 2],
		]);
	}

	public function testSpanDays() {
		$this->performSpanTest(new TimeUnit(TimeUnit::DAYS), [
			[new DateTime("20190102000000"), new DateTime("20190102000000"), 0],
			[new DateTime("20190102000000"), new DateTime("20190126000000"), 24],
			[new DateTime("20190102000000"), new DateTime("20200101000000"), 364],
			[new DateTime("20190102000000"), new DateTime("20200102000000"), 365],
			[new DateTime("20160102000000"), new DateTime("20170102000000"), 366],
			[new DateTime("20190102000000"), new DateTime("20190104020202"), 2],
		]);
	}

	public function testSpanFortnights() {
		$this->performSpanTest(new TimeUnit(TimeUnit::FORTNIGHTS), [
			[new DateTime("20190102000000"), new DateTime("20190102000000"), 0],
			[new DateTime("20190102000000"), new DateTime("20190117000000"), 1],
		]);
	}

	public function testSpanWeeks() {
		$this->performSpanTest(new TimeUnit(TimeUnit::WEEKS), [
			[new DateTime("20190102000000"), new DateTime("20190102000000"), 0],
			[new DateTime("20190102000000"), new DateTime("20190117000000"), 2],
		]);
	}

	public function testSpanMonths() {
		$this->performSpanTest(new TimeUnit(TimeUnit::MONTHS), [
			[new DateTime("20190102000000"), new DateTime("20190102000000"), 0],
			[new DateTime("20190102000000"), new DateTime("20190302000000"), 2],
			[new DateTime("20190102000000"), new DateTime("20210302000000"), 26],
		]);
	}

	public function testSpanYears() {
		$this->performSpanTest(new TimeUnit(TimeUnit::YEARS), [
			[new DateTime("20190102000000"), new DateTime("20190102000000"), 0],
			[new DateTime("19190102000000"), new DateTime("20190302000000"), 100],
			[new DateTime("19190102000000"), new DateTime("30190302000000"), 1100],
		]);
	}

	public function testSpanScores() {
		$this->performSpanTest(new TimeUnit(TimeUnit::SCORES), [
			[new DateTime("20190102000000"), new DateTime("20290102000000"), 0],
			[new DateTime("19190102000000"), new DateTime("20190302000000"), 5],
			[new DateTime("19190102000000"), new DateTime("30190302000000"), 1100/20],
		]);
	}

	public function testSpanCenturies() {
		$this->performSpanTest(new TimeUnit(TimeUnit::CENTURIES), [
			[new DateTime("20190102000000"), new DateTime("20290102000000"), 0],
			[new DateTime("19190102000000"), new DateTime("20190302000000"), 1],
			[new DateTime("19190102000000"), new DateTime("30190302000000"), 11],
		]);
	}

	public function testSpanMillennia() {
		$this->performSpanTest(new TimeUnit(TimeUnit::MILLENNIA), [
			[new DateTime("20190102000000"), new DateTime("20290102000000"), 0],
			[new DateTime("19190102000000"), new DateTime("20190302000000"), 0],
			[new DateTime("19190102000000"), new DateTime("30190302000000"), 1],
		]);
	}

	public function testInvalidSpan() {
		$from = new DateTime("20101010000000");
		$to   = new DateTime("20101010000001");

		$this->assertEquals(0, (new TimeSpan($from,$to))->getSpan(new TimeUnit(0)));
		$this->assertEquals(0, (new TimeSpan($from,$to))->getSpan(new TimeUnit(100)));
	}

	public function testFromTo() {
		$from = new DateTime("20101010");
		$to   = new DateTime("20101011");
		$span = new TimeSpan($from, $to);

		$this->assertEquals($from, $span->getFrom());
		$this->assertEquals($to, $span->getTo());
	}

	private function performSpanTest(TimeUnit $unit, array $cases) {
		foreach ($cases as $k => list($from, $to, $expect)) {
			$this->assertEquals($expect, (new TimeSpan($from, $to))->getSpan($unit));
		}
	}
}
