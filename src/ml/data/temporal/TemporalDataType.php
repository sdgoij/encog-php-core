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

use encog\util\spl\types\SplEnum;

/**
 * The type of data requested.
 */
class TemporalDataType extends SplEnum {
	/**
	 * Data in its raw, unmodified form.
	 */
	const RAW = 1;
	/**
	 * The percent change.
	 */
	const PERCENT_CHANGE = 2;
	/**
	 * The difference change.
	 */
	const DELTA_CHANGE = 3;

	public static $RAW;
	public static $PERCENT_CHANGE;
	public static $DELTA_CHANGE;
}

TemporalDataType::$RAW = new TemporalDataType(TemporalDataType::RAW);
TemporalDataType::$PERCENT_CHANGE = new TemporalDataType(TemporalDataType::PERCENT_CHANGE);
TemporalDataType::$DELTA_CHANGE = new TemporalDataType(TemporalDataType::DELTA_CHANGE);
