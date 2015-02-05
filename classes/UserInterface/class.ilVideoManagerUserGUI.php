<?php
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerPlugin.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerSubscription.php');
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
    /**
     * @var ilObjUser
     */
    protected $usr;

    public function __construct()
    {
        global $tpl, $ilUser, $ilCtrl, $ilToolbar;

        $this->usr = $ilUser;
        $this->pl = ilVideoManagerPlugin::getInstance();
        $this->tpl = $tpl;
        $this->ctrl = $ilCtrl;
        $this->toolbar = $ilToolbar;

        $this->pl->updateLanguageFiles();
    }

    public function executeCommand()
    {

        $cmd = $this->ctrl->getCmd('view');

        if($cmd == 'view')
        {
            unset($_SESSION['search_value']);

        }
        $this->prepareOutput();

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
            case 'subscribe':
                $this->subscribe();
                break;
            case 'unsubscribe':
                $this->unsubscribe();
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
        $this->tpl->addCss('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/templates/css/search_table.css');
        $this->tpl->addCss('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/templates/css/video_player.css');

        $textinput = new ilTextInputGUI('search_input', 'search_value');

        if(array_key_exists('search_value', $_POST)){
            $_SESSION['search_value'] = $_POST['search_value'];
            $this->ctrl->clearParameters($this);
            $textinput->setValue($_POST['search_value']);
        }elseif($_GET['search_value']){
            $this->ctrl->saveParameter($this, 'search_method');
            $this->ctrl->saveParameter($this, 'search_value');
            if($_GET['search_method'] != 'category'){
                $textinput->setValue($_GET['search_value']);
            }else{
                $textinput->setValue(ilVideoManagerFolder::find($_GET['search_value'])->getTitle());
            }
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
        $this->tpl->addBlockFile('ADM_CONTENT', 'search_gui', 'tpl.search_gui.html', 'Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager');
        $this->tpl->setCurrentBlock('search_gui');

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



        unset($_SESSION['table']);
        $search_results = new ilVideoManagerVideoTableGUI($this, $options, $video);
        $this->tpl->setVariable('TABLE', $search_results->getHTML());

        if($_GET['search_method'] == 'category' && !$_POST['search_value'])
        {
            $this->tpl->setVariable('CHANNEL', "Channel: '" . ilVideoManagerFolder::find($_GET['search_value'])->getTitle() . "'");

            if(ilVideoManagerSubscription::isSubscribed($this->usr->getId(), $_GET['search_value']))
            {
                $this->ctrl->saveParameter($this, 'video_tbl_table_nav');
                $this->tpl->setVariable('SUBSCRIBE_LINK', $this->ctrl->getLinkTarget($this, 'unsubscribe'));
                $this->tpl->setVariable('SUBSCRIBE', $this->pl->txt('tbl_unsubscribe'));
            }
            else
            {
                $this->ctrl->saveParameter($this, 'video_tbl_table_nav');
                $this->tpl->setVariable('SUBSCRIBE_LINK', $this->ctrl->getLinkTarget($this, 'subscribe'));
                $this->tpl->setVariable('SUBSCRIBE', $this->pl->txt('tbl_subscribe'));
            }

        }
    }

    protected function subscribe()
    {
        $subscription = new ilVideoManagerSubscription();
        $subscription->setUsrId($this->usr->getId());
        $subscription->setCatId($_GET['search_value']);
        $subscription->create();

        ilUtil::sendSuccess($this->pl->txt('msg_subscribed_successfully'), true);
        $this->ctrl->saveParameter($this, 'video_tbl_table_nav');
        $this->ctrl->redirect($this, 'performSearch');
    }

    protected function unsubscribe()
    {
        $subscription = ilVideoManagerSubscription::where(array('usr_id' => $this->usr->getId(), 'cat_id' => $_GET['search_value']))->first();
        $subscription->delete();

        ilUtil::sendSuccess($this->pl->txt('msg_unsubscribed_successfully'), true);
        $this->ctrl->saveParameter($this, 'video_tbl_table_nav');
        $this->ctrl->redirect($this, 'performSearch');
    }
} 