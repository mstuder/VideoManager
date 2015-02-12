<?php
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerObject.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerVideo.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerFolder.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerTree.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerPlugin.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerSubscription.php');
require_once('./Services/Mail/classes/class.ilMail.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/Administration/class.ilVideoManagerTreeExplorerGUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/Administration/class.ilVideoManagerAdminTableGUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/Administration/class.ilVideoManagerVideoFormGUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/Administration/class.ilVideoManagerVideoDetailsGUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/Administration/class.ilVideoManagerFolderFormGUI.php');
require_once('./Services/MainMenu/classes/class.ilMainMenuGUI.php');

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
    public $tabs;
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
     * @var ilVideoManagerVideo|ilVideoManagerFolder
     */
    public $object;
    /**
     * @var ilVideoManagerPlugin
     */
    protected $pl;


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

        $_GET['node_id'] ? $this->object = ilVideoManagerObject::find($_GET['node_id']) : $this->object = ilVideoManagerObject::__getRootFolder();

        $this->pl->updateLanguageFiles();
    }

    public function executeCommand()
    {
        if(!$_GET['node_id'])
        {
            $_GET['node_id'] = ilVideoManagerObject::__getRootFolder()->getId();
        }
        if(ilVideoManagerObject::__getTypeForId($_GET['node_id']) == 'vid')
        {
            $this->object = new ilVideoManagerVideo($_GET['node_id']);
        }else{
            $this->object = new ilVideoManagerFolder($_GET['node_id']);
        }
        $this->prepareOutput();
        $this->checkPermission();

        $cmd = $this->ctrl->getCmd('view');

        //Otherwise move-Objects would not work
        if($cmd != "cut" && $cmd != "moveMultiple")
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
            case 'deleteMultiple':
                $this->confirmDelete();
                break;
            case 'confirmDelete':
                $this->confirmDelete();
                break;
            case 'cancel':
                $this->cancel();
                break;
            case 'editfld':
                $this->editFolder();
                break;
            case 'editvid':
                $this->editVideo();
                break;
            case 'cut':
                $this->cut();
                break;
            case 'moveMultiple':
                $this->cut();
                break;
            case 'performPaste':
                $this->performPaste();
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
        //create 'add_item' button
        $ov_id = "il_add_new_item_ov";
        $ov_trigger_id = $ov_id."_tr";

        include_once "Services/UIComponent/Overlay/classes/class.ilOverlayGUI.php";
        $ov = new ilOverlayGUI($ov_id);
        $ov->add();

        $ov->addTrigger($ov_trigger_id, "click", $ov_trigger_id, false, "tl", "tr");        // trigger

        $this->toolbar->addButton($this->pl->txt("admin_add_new_item"), "#", "", "",        // toolbar
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

    public function showTree()
    {
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
    protected function prepareOutput()
    {
        $this->addAdminLocatorItems();
        $this->tpl->setLocator();
        $this->setTitleAndDescription();
    }

    /**
     * called by prepare output
     */
    protected function setTitleAndDescription()
    {
        $this->tpl->setTitle($this->object->getTitle());
        $this->tpl->setDescription($this->object->getDescription(100));
        $this->tpl->setTitleIcon($this->object->getIcon());
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
//            $this->ctrl->setParameterByClass("ilVideoManagerAdminGUI", "node_id", $this->ctrl->getParameterArray($this)['node_id']);
        }
    }

    protected function addFolder()
    {
        $form = new ilVideoManagerFolderFormGUI($this, 'create');
        $this->tpl->setContent($form->getHTML());
    }

    protected function addVideo()
    {
        $form_gui = new ilVideoManagerVideoFormGUI($this, new ilVideoManagerVideo());
        $this->tpl->setContent($form_gui->getHTML());
    }

    /**
     * @description for AJAX Drag&Drop Fileupload (Video)
     */
    protected function create()
    {
        $form = new ilVideoManagerVideoFormGUI($this, new ilVideoManagerVideo());
        $form->setValuesByPost();
        $response = $form->saveObject();
        header('Vary: Accept');
        header('Content-type: text/plain');
        require_once('./Services/JSON/classes/class.ilJsonUtil.php');
        echo ilJsonUtil::encode($response);
        exit;

    }

    protected function createFolder(){
        $form = new ilVideoManagerFolderFormGUI($this, 'create');
        $form->setValuesByPost();
        if(!$form->createFolder())
        {
            $this->addFolder();
        }
    }

    protected function confirmDelete()
    {
        $items_html = '';
        if($_POST['selected_cmd'] == 'deleteMultiple')
        {
            //none selected
            if(!$_POST['id'])
            {
                $this->ctrl->redirect($this, 'view');
            }

            //Check if one of the items is still being converted
            if(!ilVideoManagerObject::__checkConverting($_POST['id']))
            {
                ilUtil::sendFailure($this->pl->txt('msg_deletion_failed'), true);
                $this->ctrl->redirect($this, 'view');
            }


            foreach($_POST['id'] as $key => $id)
            {
                $obj = new ilVideoManagerObject($id);
                $items_html .= ilUtil::img($obj->getIcon(true)) . " " . $obj->getTitle() . '</br>';
            }
        }
        else
        {
            //Check if one of the items is still being converted
            if(!ilVideoManagerObject::__checkConverting($_GET['target_id']))
            {
                ilUtil::sendFailure($this->pl->txt('msg_deletion_failed'), true);
                $this->ctrl->redirect($this, 'view');
            }

            $this->ctrl->setParameter($this, 'target_id', $_GET['target_id']);
            $obj = new ilVideoManagerObject($_GET['target_id']);
            $items_html = ilUtil::img($obj->getIcon(true)) . " " . $obj->getTitle() . '</br>';
        }

        $this->tabs->clearTargets();
        $this->tabs->setBackTarget($this->pl->txt('common_back'), $this->ctrl->getLinkTarget($this, 'view'));
        ilUtil::sendQuestion($this->pl->txt('admin_confirm_delete'));

        $toolbar = new ilToolbarGUI();
        $toolbar->addButton($this->pl->txt('common_confirm'), $this->ctrl->getLinkTarget($this, 'delete'));
        $toolbar->addButton($this->pl->txt('common_cancel'), $this->ctrl->getLinkTarget($this, 'cancel'));

        $this->tpl->setContent($items_html . '</br>' . $toolbar->getHTML());
    }

    protected function cancel()
    {
        $this->ctrl->redirect($this, 'view');
    }

    protected function delete()
    {
        $ids = array();
        if($_SESSION['post_vars']['selected_cmd'] == 'deleteMultiple')
        {
            $ids = $_SESSION['post_vars']['id'];
        }
        else
        {
            $ids[] = $_GET['target_id'];
        }

        foreach($ids as $id)
        {
            $subtree = $this->tree->getSubTree($this->tree->getNodeData($id));
            foreach($subtree as $node)
            {
                $object = new ilVideoManagerObject($node['id']);
                $object->delete();
                $this->tree->_removeEntry(1, $node['id'], 'vidm_tree');
            }
        }

        $this->ctrl->redirect($this, 'view');
    }

    protected function editFolder()
    {
        $form = new ilVideoManagerFolderFormGUI($this, 'edit');
        $this->tpl->setContent($form->getHTML());
    }

    protected function saveFolder()
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

    protected function editVideo()
    {
        if(!ilVideoManagerObject::__checkConverting($_GET['target_id']))
        {
            ilUtil::sendInfo($this->pl->txt('msg_edit_vid_failed'), true);
            $this->ctrl->redirect($this, 'view');
        }
        $form = new ilVideoManagerVideoFormGUI($this, new ilVideoManagerVideo($_GET['target_id']));
        $form->fillForm();
        $this->tpl->setContent($form->getHTML());
    }

    protected function updateVideo()
    {
        $form = new ilVideoManagerVideoFormGUI($this, new ilVideoManagerVideo($_GET['target_id']));
        $form->setValuesByPost();
        if(!$form->saveObject())
        {
            $this->editVideo();
        }
    }

    protected function cut()
    {
        $this->tabs->setBackTarget($this->pl->txt('common_back'), $this->ctrl->getLinkTarget($this, 'showFolderContent'));
        ilUtil::sendInfo($this->pl->txt('msg_choose_folder'));
        $expl_tree = new ilVideoManagerTreeExplorerGUI('vidm_explorer', 'ilVideoManagerAdminGUI', 'performPaste', $this->tree);
        $expl_tree->setTypeWhiteList(array('fld'));
        $subtree = array();

        if($_POST['selected_cmd'] == 'moveMultiple')
        {
            //none selected
            if(!$_POST['id'])
            {
                $this->ctrl->redirect($this, 'view');
            }

            //Check if one of the items is still being converted
            if(!ilVideoManagerObject::__checkConverting($_POST['id']))
            {
                ilUtil::sendFailure($this->pl->txt('msg_move_failed'), true);
                $this->ctrl->redirect($this, 'view');
            }

            foreach($_POST['id'] as $key => $id)
            {
                $subtree = array_merge($subtree, $this->tree->getSubTree($this->tree->getNodeData($id)));
            }
        }
        else
        {
            //Check if one of the items is still being converted
            if(!ilVideoManagerObject::__checkConverting($_GET['target_id']))
            {
                ilUtil::sendFailure($this->pl->txt('msg_move_failed'), true);
                $this->ctrl->redirect($this, 'view');
            }
            $subtree = $this->tree->getSubTree($this->tree->getNodeData($_GET['target_id']));
        }

        $expl_tree->setIgnoreSubTree($subtree);
        $this->tpl->setContent($expl_tree->getHTML());
    }

    protected function performPaste()
    {
        $ids = array();
        if($_SESSION['post_vars']['selected_cmd'] == 'moveMultiple')
        {
            $ids = $_SESSION['post_vars']['id'];
        }
        else
        {
            $ids[] = $_GET['target_id'];
        }

        foreach($ids as $id)
        {
            $obj = new ilVideoManagerObject($id);
            $old_path = $obj->getPath();
            $this->tree->_removeEntry(1, $id, 'vidm_tree');
            $this->tree->insertNode($id, $_GET['node_id']);
            rename($old_path, $obj->getPath());
        }

        $this->ctrl->redirect($this, 'view');
    }

    public function notifyUsers($video)
    {
        $subscriptions = ilVideoManagerSubscription::where(array('cat_id' => $this->tree->getParentId($video->getId())));
        $mail = new ilMail(ANONYMOUS_USER_ID);
        foreach($subscriptions->get() as $subscription)
        {
            $subject = $this->getNotificationSubject($subscription);
            $message = $this->getNotificationMessage($subscription, $video);
            $mail->sendMail(
                ilObjUser::_lookupLogin($subscription->getUsrId()), '', '',
                $subject,
                $message,
                array(), array("system")
            );
        }
    }

    protected function getNotificationSubject($subscription)
    {
        $lng = ilObjUser::_lookupLanguage($subscription->getUsrId());

        return $this->pl->txt("mail_subject_".$lng) . " '" . ilVideoManagerFolder::find($subscription->getCatId())->getTitle() . "'";
    }

    protected function getNotificationMessage($subscription, $video)
    {
        $lng = ilObjUser::_lookupLanguage($subscription->getUsrId());
        $this->pl->loadLanguageModule();

        $message = '';
        $message .= ilMail::getSalutation($subscription->getUsrId(), new ilLanguage($lng));

        $message .= "\n\n";
        $message .= $this->pl->txt("mail_new_upload_".$lng);
        $message .= "\n\n";
        $message .= $this->pl->txt("common_category_".$lng).": ".ilVideoManagerFolder::find($subscription->getCatId())->getTitle();
        $message .= "\n\n";
        $message .= $this->pl->txt("common_video").': '.$video->getTitle().'';

        $message .= "\n\n";
        $message .= $this->pl->txt('mail_view_video');
        $this->ctrl->setParameterByClass('ilVideoManagerUserGUI', 'node_id', $video->getId());
        $message .= ' ' . ilUtil::_getHttpPath().'/'.$this->ctrl->getLinkTargetByClass('ilVideoManagerUserGUI', 'playVideo');

        $message .= ilMail::_getInstallationSignature();
        return $message;
    }

    protected function checkPermission()
    {
        if(!ilMainMenuGUI::_checkAdministrationPermission())
        {
            ilUtil::sendFailure($this->pl->txt('msg_no_permission'), true);
            $this->ctrl->redirectByClass('ilRouterGUI');
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