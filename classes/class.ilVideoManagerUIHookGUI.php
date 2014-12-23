<?php
require_once('./Services/UIComponent/classes/class.ilUIHookPluginGUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/CtrlMainMenu/classes/EntryTypes/Admin/class.ctrlmmEntryAdmin.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/CtrlMainMenu/classes/Menu/class.ctrlmmMenuGUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/CtrlMainMenu/classes/Entry/class.ctrlmmEntry.php');
require_once('./Services/MainMenu/classes/class.ilMainMenuGUI.php');


/**
 * Class ilVideoManagerUIHookGUI
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_IsCalledBy ilVideoManagerUIHookGUI: ilAdministrationGUI
 */
class ilVideoManagerUIHookGUI extends ilUIHookPluginGUI{

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
//        if($a_comp == 'Services/MainMenu' AND $a_part == 'main_menu_list_entries'){
//            //var_dump($a_par["main_menu_gui"]);
//        }
//        echo $a_comp;
//        echo"222";
//        echo $a_part;
//        echo"333";
    }

} 