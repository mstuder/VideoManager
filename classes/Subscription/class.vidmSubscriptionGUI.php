<?php

/**
 * Class vidmSubscriptionGUI
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class vidmSubscriptionGUI {

	public function __construct() {
		global $tpl, $ilCtrl, $ilAccess, $ilToolbar, $ilLocator, $ilTabs;

		$this->tabs = $ilTabs;
		$this->pl = ilVideoManagerPlugin::getInstance();
		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->ilAccess = $ilAccess;
		$this->ilLocator = $ilLocator;
		$this->toolbar = $ilToolbar;
		$this->tree = new ilVideoManagerTree(1);
		//$_GET['node_id'] ? $this->object = ilVideoManagerObject::find($_GET['node_id']) : $this->object = ilVideoManagerObject::__getRootFolder();
	}


	public function executeCommand() {
		ilUtil::sendInfo('jippii');
	}
}

?>
