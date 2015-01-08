<?php
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerObject.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerTree.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerPlugin.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/Administration/class.ilVideoManagerTreeExplorerGUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/Administration/class.ilVideoManagerAdminTableGUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/Administration/class.ilVideoManagerVideoFormGUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/Administration/class.ilVideoManagerVideoDetailsGUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/Administration/class.ilVideoManagerFolderFormGUI.php');
require_once('./Modules/Cloud/classes/class.ilCloudConnector.php');

/**
 * Class ilVideoManagerGUI
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_IsCalledBy ilVideoManagerAdminGUI: ilRouterGUI
 * @ilCtrl_Calls ilVideoManagerAdminGUI: ilVideoManagerAdminTableGUI
 */
class ilVideoManagerAdminGUI{

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
     * @var ilAccessHandler
     */
    protected $ilAccess;
    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;
    /**
     * @var ilLocatorGUI
     */
    protected $ilLocator;
    /**
     * @var ilVideoManagerTree
     */
    public $tree;
    /**
     * @var ilVideoManagerObject
     */
    public $object;
    /**
     * @var ilLog
     */
    protected $ilLog;
    /**
     * @var ilVideoManagerPlugin
     */
    protected $pl;


    public function __construct() {
        global $tpl, $ilCtrl, $ilAccess, $ilToolbar, $ilLocator, $lng, $ilLog;

        $this->pl = ilVideoManagerPlugin::getInstance();
        $this->tpl = $tpl;
        $this->ctrl = $ilCtrl;
        $this->ilAccess = $ilAccess;
        $this->ilLocator = $ilLocator;
        $this->toolbar = $ilToolbar;
        $this->ilLog = $ilLog;
        $this->tree = new ilVideoManagerTree(1);

        $_GET['node_id'] ? $this->object = ilVideoManagerObject::find($_GET['node_id']) : $this->object = ilVideoManagerObject::__getRootFolder();

        $this->pl->updateLanguageFiles();
    }

    public function executeCommand()
    {
        if(!$_GET['node_id'])
        {
            $_GET['node_id'] = ilVideoManagerObject::__getRootFolder()->getId();
        }
        $this->object = ilVideoManagerObject::find($_GET['node_id']);
        $this->prepareOutput();

        $cmd = $this->ctrl->getCmd('view');

        //Otherwise move-Objects would not work
        if($cmd != "cut")
        {
            $this->showTree();
        }

        switch($cmd){
            case 'addFolder':
                $this->addFolder();
                break;
            case 'addVideo':
                $this->addVideo();
                break;
            case 'create':
                $this->create();
                break;
            case 'createFolder':
                $_POST['create_type'] = 'fld';
                $this->createFolder();
                break;
            case 'showTree':
                $this->showTree();
                break;
            case 'saveFolder':
                $this->saveFolder();
                break;
            case 'delete':
                $this->delete();
                break;
            case 'confirmDelete':
                $this->confirmDelete();
                break;
            case 'cancel':
                $this->cancel();
                break;
            case 'edit':
                $this->edit();
                break;
            case 'view':
                $this->view();
                break;
            default:
                $this->$cmd();
        }
    }

    public function view() {
        switch($this->object->getType())
        {
            case 'fld':
                $this->showFolderContent();
                break;
            case 'vid':
                $this->showVideoDetails();
                break;
        }


    }

    public function showFolderContent()
    {
        global $ilToolbar;

        //create 'add_item' button
        $ov_id = "il_add_new_item_ov";
        $ov_trigger_id = $ov_id."_tr";

        include_once "Services/UIComponent/Overlay/classes/class.ilOverlayGUI.php";
        $ov = new ilOverlayGUI($ov_id);
        $ov->add();

        $ov->addTrigger($ov_trigger_id, "click", $ov_trigger_id, false, "tl", "tr");        // trigger

        $ilToolbar->addButton($this->pl->txt("admin_add_new_item"), "#", "", "",        // toolbar
            "", $ov_trigger_id, 'submit emphsubmit');

        include_once("./Services/UIComponent/GroupedList/classes/class.ilGroupedListGUI.php");
        $gl = new ilGroupedListGUI();

        $icon_path = ilUtil::getImagePath('icon_cat_s.png');
        $gl->addEntry(ilUtil::img($icon_path)." ".$this->pl->txt("admin_add_folder"), $this->ctrl->getLinkTarget($this, 'addFolder'),
            "_top");

        $icon_path = ilUtil::getImagePath('icon_mobs_s.png');
        $gl->addEntry(ilUtil::img($icon_path) . " " .$this->pl->txt("admin_add_video"), $this->ctrl->getLinkTarget($this, 'addVideo'),
            "_top");

        $this->tpl->setVariable("SELECT_OBJTYPE_REPOS", '<div id="' . $ov_id . '" class="ilOverlay ilNoDisplay">'.$gl->getHTML().'</div>');

        //list items
        $table = new ilVideoManagerAdminTableGUI($this);
        $this->tpl->setContent($table->getHTML());
    }

    public function showVideoDetails()
    {
        global $ilTabs;
        $parent_id = $this->tree->getParentId($this->object->getId());
        $this->ctrl->setParameter($this, 'node_id', $parent_id);
        $ilTabs->setBackTarget($this->pl->txt('common_back'), $this->ctrl->getLinkTarget($this, 'showFolderContent'));
        $vm_video_details = new ilVideoManagerVideoDetailsGUI($this, $this->object);
        $vm_video_details->init();
    }

    public function showTree() {
        $expl_tree = new ilVideoManagerTreeExplorerGUI('vidm_explorer', 'ilVideoManagerAdminGUI', 'showTree', $this->tree);
        $expl_tree->setTypeWhiteList(array('fld', 'vid'));
        $expl_tree->setPathOpen($_GET['node_id'] ? $_GET['node_id'] : ilVideoManagerObject::__getRootFolder()->getId());
        if(!$expl_tree->handleCommand()){
            $this->tpl->setLeftNavContent($expl_tree->getHTML());
        }
        $this->ctrl->setParameterByClass('ilVideoManagerAdminGUI', 'node_id', $this->object->getId());

    }

    /**
     * invoked by executeCommand()
     */
    public function prepareOutput(){
        $this->addAdminLocatorItems();
        $this->tpl->setLocator();
        $this->setTitleAndDescription();
    }

    /**
     * called by prepare output
     */
    public function setTitleAndDescription()
    {
        global $tpl;

        $tpl->setTitle($this->object->getTitle());
        $tpl->setDescription($this->object->getDescription());
        $tpl->setTitleIcon(ilUtil::getImagePath('icon_cat_b.png'));

    }

    /**
     * called by prepare output
     */
    protected function addAdminLocatorItems() {
        $_GET['node_id'] ? $end_node = $_GET['node_id'] : $end_node = ilVideoManagerObject::__getRootFolder()->getId();
        $path = $this->tree->getPathFull($end_node, ilVideoManagerObject::__getRootFolder()->getId());
        // add item for each node on path
        foreach ((array)$path as $key => $row) {
            $this->ctrl->setParameterByClass("ilvideomanageradmingui", 'node_id', $row["child"]);
            $this->ilLocator->addItem($row["title"], $this->ctrl->getLinkTargetByClass("ilVideoManagerAdminGUI", "view"), ilFrameTargetInfo::_getFrame("MainContent"));
            $this->ctrl->setParameterByClass("ilVideoManagerAdminGUI", "node_id", $this->ctrl->getParameterArray($this)['node_id']);
        }
    }

    function addFolder()
    {
        $form = new ilVideoManagerFolderFormGUI($this, 'create');
        $this->tpl->setContent($form->getHTML());
    }

    function addVideo()
    {
        $form_gui = new ilVideoManagerVideoFormGUI($this, new ilVideoManagerObject());
        $this->tpl->setContent($form_gui->getHTML());
    }

    /**
     * @description for AJAX Drag&Drop Fileupload (Video)
     */
    function create()
    {
        $form = new ilVideoManagerVideoFormGUI($this, new ilVideoManagerObject());
        $form->setValuesByPost();
        $response = $form->saveObject();
        header('Vary: Accept');
        header('Content-type: text/plain');
        require_once('./Services/JSON/classes/class.ilJsonUtil.php');
        echo ilJsonUtil::encode($response);
        exit;

    }

    /**
     * called by ilVideoManagerFolderFormGUI
     */
    function createFolder(){
        $form = new ilVideoManagerFolderFormGUI($this, 'create');
        $form->setValuesByPost();
        if(!$form->createFolder())
        {
            $this->addFolder();
        }
    }

    function confirmDelete()
    {
        global $ilToolbar, $ilTabs, $ilCtrl;

        $this->ctrl->setParameter($this, 'node_id', $_GET['node_id']);
        $this->ctrl->setParameter($this, 'target_id', $_GET['target_id']);

        $ilTabs->clearTargets();
        $ilTabs->setBackTarget($this->pl->txt('common_back'), $ilCtrl->getLinkTarget($this, 'view'));
        ilUtil::sendInfo($this->pl->txt('admin_confirm_delete'));

        //TODO list the items to be deleted
        $ilToolbar->addButton($this->pl->txt('common_confirm'), $ilCtrl->getLinkTarget($this, 'delete'));
        $ilToolbar->addButton($this->pl->txt('common_cancel'), $ilCtrl->getLinkTarget($this, 'cancel'));

    }

    function cancel()
    {
        $this->ctrl->redirect($this, 'view');
    }

    function delete()
    {
        $subtree = $this->tree->getSubTree($this->tree->getNodeData($_GET['target_id']));
        foreach($subtree as $node)
        {
            $object = new ilVideoManagerObject($node['id']);
            $object->delete();
            $this->tree->_removeEntry(1, $node['id'], 'vidm_tree');
        }

        $this->ctrl->redirect($this, 'view');
    }

    function edit()
    {
        $object = new ilVideoManagerObject($_GET['target_id']);
        switch($object->getType())
        {
            case 'fld':
                $this->editFolder();
                break;
            case 'vid':
                $this->editVideo();
                break;
        }
    }

    function editFolder()
    {
        $form = new ilVideoManagerFolderFormGUI($this, 'edit');
        $this->tpl->setContent($form->getHTML());
    }

    function saveFolder()
    {
        $form = new ilVideoManagerFolderFormGUI($this, 'edit');
        $form->setValuesByPost();
        $this->ctrl->saveParameterByClass('ilVideoManagerFolderFormGUI', 'target_id');
        if(!$form->saveFolder())
        {
            $this->editFolder();
        }
        $this->ctrl->redirect($this, 'view');
    }

    function editVideo()
    {
        $form = new ilVideoManagerVideoFormGUI($this, new ilVideoManagerObject($_GET['target_id']));
        $form->fillForm();
        $this->tpl->setContent($form->getHTML());
    }

    function updateVideo()
    {
        $form = new ilVideoManagerVideoFormGUI($this, new ilVideoManagerObject($_GET['target_id']));
        $form->setValuesByPost();
        if(!$form->saveObject())
        {
            $this->editVideo();
        }
    }
    /**
     * @return \ilVideoManagerTree
     */
    public function getTree()
    {
        return $this->tree;
    }

    /**
     * @return \ilVideoManagerObject
     */
    public function getObject()
    {
        return $this->object;
    }

} 