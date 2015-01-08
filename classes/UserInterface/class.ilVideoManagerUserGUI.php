<?php
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerObject.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerTree.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerPlugin.php');
require_once('./Services/Form/classes/class.ilTextInputGUI.php');
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');

/**
 * Class ilVideoManagerUserGUI
 *
 * @ilCtrl_IsCalledBy IlVideoManagerUserGUI: ilRouterGUI
 */
class ilVideoManagerUserGUI {

    /**
     * @var ilCtrl
     */
    public $ctrl;
    /**
     * @var ilTemplate
     */
    public $tpl;
    /**
     * @var ilTabsGUI
     */
    public $tabs_gui;
    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;
    /**
     * @var ilVideoManagerTree
     */
    public $tree;
    /**
     * @var ilVideoManagerPlugin
     */
    protected $pl;

    public function __construct()
    {
        global $tpl, $ilCtrl, $ilToolbar;

        $this->pl = ilVideoManagerPlugin::getInstance();
        $this->tpl = $tpl;
        $this->ctrl = $ilCtrl;
        $this->toolbar = $ilToolbar;
        $this->tree = new ilVideoManagerTree(1);

        $this->pl->updateLanguageFiles();


    }

    public function executeCommand()
    {
        $this->prepareOutput();

        $cmd = $this->ctrl->getCmd('view');

        switch($cmd)
        {
            case 'view':

        }

    }

    public function prepareOutput()
    {
        $search_field = new ilPropertyFormGUI();
        $search_field->setFormAction($this->ctrl->getFormAction($this));

        $text_input = new ilTextInputGUI('search_text', 'search_text');
        $search_field->addItem($text_input);

        $search_field->addCommandButton('search', $this->pl->txt('UI_search'));

        $this->toolbar->addText($search_field->getHTML());
//        $text_input->form
//        $this->toolbar->addText($search_field->getToolbarHTML());
//        $this->toolbar->addButton($this->pl->txt('UI_search'), );
    }
} 