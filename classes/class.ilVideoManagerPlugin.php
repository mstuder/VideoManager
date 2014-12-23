<?php
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/CtrlMainMenu/classes/Menu/class.ctrlmmMenuGUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/CtrlMainMenu/classes/Entry/class.ctrlmmEntry.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/CtrlMainMenu/classes/EntryTypes/Dropdown/class.ctrlmmEntryDropdown.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/CtrlMainMenu/classes/EntryTypes/Link/class.ctrlmmEntryLink.php');
require_once('./Services/MainMenu/classes/class.ilMainMenuGUI.php');

/**
 * Class ilVideoManagerPlugin
 *
 * @author Theodor Truffer <tt@studer-ramimann.ch>
 */
class ilVideoManagerPlugin extends ilUserInterfaceHookPlugin{

    /**
     * @return string
     */
    public function getCsvPath() {
        $path = substr(__FILE__, 0, strpos(__FILE__, 'classes')) . 'lang/';
        if (file_exists($path . 'lang_custom.csv')) {
            $file = $path . 'lang_custom.csv';
        } else {
            $file = $path . 'lang.csv';
        }

        return $file;
    }


    /**
     * @return string
     */
    public function getAjaxLink() {
        return false;
    }


    /**
     * @param $key
     *
     * @return mixed
     */
    public function getDynamicTxt($key) {
        return ilDynamicLanguage::getInstance($this, ilDynamicLanguage::MODE_PROD)->txt($key);
    }


    /**
     * @var ilSubscriptionPlugin
     */
    protected static $instance;


    /**
     * @return ilVideoManagerPlugin
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    /**
     * @return string
     */
    public function getPluginName() {
        return 'VideoManager';
    }


    /**
     * @return bool
     */
    public static function checkPreconditions() {
        /**
         * @var $ilCtrl ilCtrl
         */
        $path = strstr(__FILE__, 'Services', true) . 'Libraries/ActiveRecord/';
        global $ilCtrl;
        if ($ilCtrl->lookupClassPath('ilRouterGUI') === NULL OR !is_file($path . 'class.ActiveRecord.php') OR !is_file($path
                . 'class.ActiveRecordList.php')
        ) {
            return false;
        }

        return true;
    }


    /**
     * @return bool
     */
    public function beforeActivation() {
        // TODO: put in UIHookGUI
        global $ilCtrl;
        foreach(ctrlmmEntry::getAll(false) as $entry)
        {
            if($entry->getTitle() == 'Video Manager'){
                return self::checkPreconditions();
            }
        }

        if(ilMainMenuGUI::_checkAdministrationPermission())
        {
            $entry = new ctrlmmEntryDropdown();
            $entry->setTitle('Video Manager');
            $entry->setTranslations(array('en' => 'Video Manager'));
            $entry->create();

            $subentry_channels = new ctrlmmEntryLink();
            $subentry_channels->setTranslations(array('en' => 'Channels'));
            $subentry_channels->setLink($ilCtrl->getLinkTargetByClass(array('ilroutergui', 'ilvideomanagerusergui'),  'view'));
            $subentry_channels->setTarget('_top');
            $subentry_channels->setParent($entry->getId());
            $subentry_channels->create();

            $subentry_admin = new ctrlmmEntryLink();
            $subentry_admin->setTranslations(array('en' => 'Administration'));
            $subentry_admin->setLink($ilCtrl->getLinkTargetByClass(array('ilroutergui', 'ilvideomanageradmingui'), 'view'));
            $subentry_admin->setTarget('_top');
            $subentry_admin->setParent($entry->getId());
            $subentry_admin->create();


            $entry->setEntries(array($subentry_channels, $subentry_admin));
            $entry->update();
        }else{
            $entry = new ctrlmmEntryLink();
            $entry->setTranslations(array('en' => 'Video Manager'));
            $entry->setLink($ilCtrl->getLinkTargetByClass('ilvideomanagerusergui', 'view'));
            $entry->create();
        }


        return self::checkPreconditions();
    }

}