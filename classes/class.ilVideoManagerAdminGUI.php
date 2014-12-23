<?php
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerObject.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerTree.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerPlugin.php');
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');

/**
 * Class ilVideoManagerGUI
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_IsCalledBy ilVideoManagerAdminGUI: ilRouterGUI
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

        $lng->loadLanguageModule("vidm");
    }

    public function executeCommand()
    {
        $_GET['node_id'] ? $this->object = ilVideoManagerObject::find($_GET['node_id']) : $this->object = ilVideoManagerObject::__getRootFolder();
        $this->setTitleAndDescription();

        $cmd = $this->ctrl->getCmd('view');
        $next_class = $this->ctrl->getNextClass($this);

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

                break;
            case 'createFolder':
                $_POST['create_type'] = 'fld';
                $this->create();
                break;
            case 'view':
                $this->showContent();
                break;
            default:
                $this->showContent();
        }
    }

    public function showContent() {
        global $ilToolbar;

        include_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
        $adv = new ilAdvancedSelectionListGUI();
        $adv->setListTitle($this->pl->txt("admin_add_new_item"));

        $adv->addItem($this->pl->txt('admin_add_folder'), '', $this->ctrl->getLinkTarget($this, 'addFolder'), ilUtil::img(ilUtil::getImagePath('icon_cat_b.png')));
        $adv->addItem($this->pl->txt('admin_add_video'), '', $this->ctrl->getLinkTarget($this, 'addVideo'), ilUtil::img(ilUtil::getImagePath('icon_mobs_b.png')));

        $ilToolbar->addText($adv->getHTML());
    }

    /**
     * initCreationForms
     *
     * We override the method of class.ilObjectGUI because we have no copy functionality
     * at the moment
     *
     * @param string $a_new_type
     *
     * @return array
     */
    protected function initCreationForms($a_new_type)
    {
        $forms = array(
            self::CFORM_NEW => $this->initCreateForm($a_new_type),
            self::CFORM_IMPORT => $this->initImportForm($a_new_type),
        );

        return $forms;
    }

    public function showPossibleSubObjects() {
        $gui = new ilObjectAddNewItemGUI($this->object->getRefId());
        $gui->setMode(ilObjectDefinition::MODE_ADMINISTRATION);
        $gui->setCreationUrl("ilias.php?ref_id=" . $_GET["ref_id"]
            . "&admin_mode=settings&cmd=create&baseClass=ilAdministrationGUI");
        $gui->render();
    }


    public function showTree() {
//        $tree = new ilOrgUnitExplorerGUI("orgu_explorer", "ilObjOrgUnitGUI", "showTree", new ilTree(1));
//        $tree->setTypeWhiteList(array( "orgu" ));
//        if (! $tree->handleCommand()) {
//            $this->tpl->setLeftNavContent($tree->getHTML());
//        }
//        $this->ctrl->setParameterByClass("ilObjOrgUnitGUI", "ref_id", $_GET["ref_id"]);
    }

    /**
     * called by prepare output
     */
    public function setTitleAndDescription() {
        global $tpl;
        # all possible create permissions
        //$possible_ops_ids = $rbacreview->getOperationsByTypeAndClass('orgu', 'create');
        $tpl->setTitle($this->object->getTitle());
        $tpl->setDescription($this->object->getDescription());
        $tpl->setTitleIcon(ilUtil::getImagePath('icon_cat_b.png'));

    }

    function addFolder()
    {
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->pl->txt('form_add_folder'));
        $title = new ilTextInputGUI($this->pl->txt('common_title'), 'title');
        $description = new ilTextInputGUI($this->pl->txt('common_description'), 'desc');
        $form->setItems(array($title, $description));
        $form->setFormAction($this->ctrl->getLinkTarget($this, 'createFolder'));
        $form->addCommandButton('createFolder', $this->pl->txt('form_create_folder'));
        $form->addCommandButton('cancel', $this->pl->txt('common_cancel'));
        $this->tpl->setContent($form->getHTML());
    }

    function create()
    {
        $newObject = new ilVideoManagerObject();
        $newObject->setTitle($_POST['title']);
        $newObject->setDescription($_POST['desc']);
        $newObject->setType($_POST['create_type']);
        $newObject->create();
        $this->tree->insertNode($newObject->getId(), $this->object->getId());
        ilUtil::sendSuccess($this->pl->txt('folder_save_succeed'), true);
        $this->ctrl->setParameterByClass('ilvideomanageradmingui', 'node_id', $newObject->getId());
        $this->ctrl->redirect($this, 'view');
    }



} 