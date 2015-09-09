<?php

/**
 * Class ilVideoManagerAccess
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilVideoManagerAccess {

	public static function checkAccess($user_id) {
		if (! self::isActive()) {
			return false;
		}

	}


	/**
	 * @return mixed
	 */
	public static function isActive() {
		return ilVideoManagerPlugin::getInstance()->isActive();
	}
}

?>
