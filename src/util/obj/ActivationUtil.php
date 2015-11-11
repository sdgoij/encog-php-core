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
namespace encog\util\obj;

use encog\engine\network\activation\ActivationFunction;
use encog\util\csv\CSVFormat;
use encog\util\csv\NumberList;

final class ActivationUtil {
	public static function generateActivationFactory(string $name, ActivationFunction $af): string {
		$result = strtoupper($name);
		if (count($af->getParams())) {
			$result .= sprintf("[%s]", NumberList::toList(CSVFormat::$egFormat, $af->getParams()));
		}
		return $result;
	}
}
