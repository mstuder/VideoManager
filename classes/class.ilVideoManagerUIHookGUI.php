<?php
require_once('./Services/UIComponent/classes/class.ilUIHookPluginGUI.php');

/**
 * Class ilVideoManagerUIHookGUI
 *
 * @author            Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_IsCalledBy ilVideoManagerUIHookGUI: ilAdministrationGUI
 */
class ilVideoManagerUIHookGUI extends ilUIHookPluginGUI {

	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var $ilTabs
	 */
	protected $tabs;
	/**
	 * @var ilAccessHandler
	 */
	protected $access;


	public function __construct() {
		global $ilCtrl, $ilTabs, $ilAccess;
		$this->ctrl = $ilCtrl;
		$this->tabs = $ilTabs;
		$this->access = $ilAccess;
		$this->pl = ilVideoManagerPlugin::getInstance();
	}


	/**
	 * @param       $a_comp
	 * @param       $a_part
	 * @param array $a_par
	 *
	 * @return array
	 */
	public function getHTML($a_comp, $a_part, $a_par = array()) {
	}
}