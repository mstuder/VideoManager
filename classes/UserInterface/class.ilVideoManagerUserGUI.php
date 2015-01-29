<?php
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerObject.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerTree.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerPlugin.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/UserInterface/class.ilVideoManagerVideoTableGUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/UserInterface/class.ilVideoManagerPlayVideoGUI.php');
require_once('./Services/Form/classes/class.ilTextInputGUI.php');
require_once('./Services/Search/classes/class.ilSearchBaseGUI.php');
require_once('./Services/Search/classes/class.ilSearchGUI.php');
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');

/**
 * Class ilVideoManagerUserGUI
 *
 * @ilCtrl_IsCalledBy ilVideoManagerUserGUI: ilRouterGUI
 * @ilCtrl_Calls ilVideoManagerUserGUI: ilVideoManagerVideoTableGUI, ilVideoManagerPlayVideoGUI, ilSearchGUI, ilPropertyFormGUI
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
                $this->view();
                break;
            case 'performSearch':
                $this->performSearch();
                break;
            case 'playVideo':
                $this->playVideo();
                break;

        }

    }

    function view()
    {
        $options = array(
            'cmd' => 'latest_uploads',
            'sort_create_date' => 'ASC',
            'limit' => 8,
        );
        $this->tpl->addCss('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/templates/css/search_table.css');
        $starter_gui = new ilVideoManagerVideoTableGUI($this, $options);
        $this->tpl->setContent($starter_gui->getHTML());
    }

    function playVideo()
    {
        $video_gui = new ilVideoManagerPlayVideoGUI($this);
        $video_gui->init();
    }

    public function prepareOutput()
    {
        $this->tpl->addCss('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/templates/css/video_player.css');
        $this->toolbar->addInputItem(new ilTextInputGUI('search_input', 'search_value'));
        $this->toolbar->addFormButton($this->pl->txt('common_search'), 'performSearch');
        $this->toolbar->setFormAction($this->ctrl->getLinkTarget($this, 'performSearch'));
    }

    public function performSearch()
    {
        $video = null;
        if($_GET['node_id'])
        {
            $video = new ilVideoManagerVideo($_GET['node_id']);
        }

        if($_GET['search_value'])
        {
            $search = array(
            'value' => $_GET['search_value'],
            'method' => $_GET['search_method']);
        }else{
            $search = array(
                'value' => $_POST['search_value'],
                'method' => 'all');
        }

        $options = array(
            'cmd' => 'search_results',
            'search' => $search,
            'sort_create_date' => 'ASC',
            'limit' => 10
        );

        $this->tpl->addCss('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/templates/css/video_player.css');
        $this->tpl->addCss('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/templates/css/search_table.css');

        $search_results = new ilVideoManagerVideoTableGUI($this, $options, $video);
        $this->tpl->setContent($search_results->getHTML());
    }
} 