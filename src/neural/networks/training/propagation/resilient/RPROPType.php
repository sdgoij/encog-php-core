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
namespace encog\neural\networks\training\propagation\resilient;

use encog\util\spl\types\SplEnum;

/**
 * Allows the type of RPROP to be defined. RPROPp is the classic RPROP.
 */
class RPROPType extends SplEnum {
	/**
	 * RPROP+ : The classic RPROP algorithm.  Uses weight back tracking.
	 */
	const RPROPp = 1;
	/**
	 * RPROP- : No weight back tracking.
	 */
	const RPROPm = 2;
	/**
	 * iRPROP+ : New weight back tracking method, some consider this to be
	 * the most advanced RPROP.
	 */
	const iRPROPp = 3;
	/**
	 * iRPROP- : New RPROP without weight back tracking.
	 */
	const iRPROPm = 4;
	/**
	 * ARPROP : Non-linear Jacobi RPROP.
	 */
	const ARPROP = 5;

	public static $RPROPp;
	public static $RPROPm;
	public static $iRPROPp;
	public static $iRPROPm;
	public static $ARPROP;

	public function __toString() {
		if ($this == self::$RPROPp) {
			return "RPROP+";
		}
		if ($this == self::$RPROPm) {
			return "RPROP-";
		}
		if ($this == self::$iRPROPp) {
			return "iRPROP+";
		}
		if ($this == self::$iRPROPm) {
			return "iRPROP-";
		}
		if ($this == self::$ARPROP) {
			return "ARPROP";
		}
		return "???";
	}
}

RPROPType::$RPROPp  = new RPROPType(RPROPType::RPROPp);
RPROPType::$RPROPm  = new RPROPType(RPROPType::RPROPm);
RPROPType::$iRPROPp = new RPROPType(RPROPType::iRPROPp);
RPROPType::$iRPROPm = new RPROPType(RPROPType::iRPROPm);
RPROPType::$ARPROP  = new RPROPType(RPROPType::ARPROP);
