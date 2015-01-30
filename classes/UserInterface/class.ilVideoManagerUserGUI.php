<?php
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerPlugin.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/UserInterface/class.ilVideoManagerVideoTableGUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/UserInterface/class.ilVideoManagerPlayVideoGUI.php');
require_once('./Services/Form/classes/class.ilTextInputGUI.php');

/**
 * Class ilVideoManagerUserGUI
 *
 * @ilCtrl_IsCalledBy ilVideoManagerUserGUI: ilRouterGUI
 * @ilCtrl_Calls ilVideoManagerUserGUI: ilVideoManagerVideoTableGUI, ilVideoManagerPlayVideoGUI
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
            'cmd' => 'view',
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
        $textinput = new ilTextInputGUI('search_input', 'search_value');

        if(array_key_exists('search_value', $_POST)){
            $_SESSION['search_value'] = $_POST['search_value'];
            $this->ctrl->clearParameters($this);
            $textinput->setValue($_POST['search_value']);
        }elseif($_GET['search_value']){
            $this->ctrl->saveParameter($this, 'search_method');
            $this->ctrl->saveParameter($this, 'search_value');
            $textinput->setValue($_GET['search_value']);
        }elseif(array_key_exists('search_value', $_SESSION)){
            $textinput->setValue($_SESSION['search_value']);
        }

        $this->toolbar->setId('search');
        $this->toolbar->addInputItem($textinput);
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

        if(array_key_exists('search_value', $_POST))
        {
            $search = array(
                'value' => $_POST['search_value'],
                'method' => 'all');
        }
        elseif($_GET['search_value'])
        {
        $search = array(
            'value' => $_GET['search_value'],
            'method' => $_GET['search_method']);
        }
        elseif(array_key_exists('search_value', $_SESSION))
        {
            $search = array(
                'value' => $_SESSION['post_vars']['search_value'],
                'method' => 'all');
        }
        else
        {
            ilUtil::sendFailure('Error: no search value given');
            return false;
        }

        $options = array(
            'cmd' => 'performSearch',
            'search' => $search,
            'sort_create_date' => 'ASC',
        );

        $this->tpl->addCss('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/templates/css/video_player.css');
        $this->tpl->addCss('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/templates/css/search_table.css');

        unset($_SESSION['table']);
        $search_results = new ilVideoManagerVideoTableGUI($this, $options, $video);
        $this->tpl->setContent($search_results->getHTML());
    }
} 