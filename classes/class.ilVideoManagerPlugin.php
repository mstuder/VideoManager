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

    public function updateLanguageFiles() {
        setlocale(LC_ALL, 'de_DE.utf8');
        ini_set('auto_detect_line_endings', true);
        $path = substr(__FILE__, 0, strpos(__FILE__, 'classes')) . 'lang/';
        if (file_exists($path . 'lang_custom.csv')) {
            $file = $path . 'lang_custom.csv';
        } else {
            $file = $path . 'lang.csv';
        }
        $keys = array();
        $new_lines = array();

        foreach (file($file) as $n => $row) {
            if ($n == 0) {
                $keys = str_getcsv($row, ";");
                continue;
            }
            $data = str_getcsv($row, ";");;
            foreach ($keys as $i => $k) {
                if ($k != 'var' AND $k != 'part') {
                    if ($data[1] != '') {
                        $new_lines[$k][] = $data[0] . '_' . $data[1] . '#:#' . $data[$i];
                    } else {
                        $new_lines[$k][] = $data[0] . '#:#' . $data[$i];
                    }
                }
            }
        }
        $start = '<!-- language file start -->' . PHP_EOL;
        $status = true;

        foreach ($new_lines as $lng_key => $lang) {
            $status = file_put_contents($path . 'ilias_' . $lng_key . '.lang', $start . implode(PHP_EOL, $lang));
        }

        if (!$status) {
            ilUtil::sendFailure('Language-Files coul\'d not be written');
        }
        $this->updateLanguages();
    }


    /**
     * @return bool
     */
    public function beforeActivation() {
//         TODO: put in UIHookGUI
        global $ilCtrl;

        $this->updateLanguageFiles();

//        foreach(ctrlmmEntry::get() as $entry)
//        {
//            if($entry->getTitle() == 'Video Manager'){
//                return self::checkPreconditions();
//            }
//        }
//
//        if(ilMainMenuGUI::_checkAdministrationPermission())
//        {
//            $entry = new ctrlmmEntryDropdown();
//            $entry->setTitle('Video Manager');
//            $entry->setTranslations(array('en' => 'Video Manager'));
//            $entry->create();
//
//            $subentry_channels = new ctrlmmEntryLink();
//            $subentry_channels->setTranslations(array('en' => 'Channels'));
//            $subentry_channels->setLink($ilCtrl->getLinkTargetByClass(array('ilroutergui', 'ilvideomanagerusergui'),  'view'));
//            $subentry_channels->setTarget('_top');
//            $subentry_channels->setParent($entry->getId());
//            $subentry_channels->create();
//
//            $subentry_admin = new ctrlmmEntryLink();
//            $subentry_admin->setTranslations(array('en' => 'Administration'));
//            $subentry_admin->setLink($ilCtrl->getLinkTargetByClass(array('ilroutergui', 'ilvideomanageradmingui'), 'view'));
//            $subentry_admin->setTarget('_top');
//            $subentry_admin->setParent($entry->getId());
//            $subentry_admin->create();
//
//
//            $entry->setEntries(array($subentry_channels, $subentry_admin));
//            $entry->update();
//        }else{
//            $entry = new ctrlmmEntryLink();
//            $entry->setTranslations(array('en' => 'Video Manager'));
//            $entry->setLink($ilCtrl->getLinkTargetByClass('ilvideomanagerusergui', 'view'));
//            $entry->create();
//        }


        return self::checkPreconditions();
    }

    public static function loadActiveRecord() {
        if (ctrlmm::is50()) {
            require_once('./Services/ActiveRecord/class.ActiveRecord.php');
        } else {
            require_once('./Customizing/global/plugins/Libraries/ActiveRecord/class.ActiveRecord.php');
        }
    }


    /**
     * @param $usr_id
     * @return ilLanguage
     */
    public function loadLanguageForUser($usr_id)
    {
        $lng = ilObjUser::_lookupLanguage($usr_id);
        $ilLanguage = new ilLanguage($lng);
        $ilLanguage->loadLanguageModule("ui_uihk_video_man");
        return $ilLanguage;
    }

}