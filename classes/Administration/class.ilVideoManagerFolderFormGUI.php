<?php
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerObject.php');


/**
 * Class ilVideoManagerFolderFormGUI
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_IsCalledBy ilVideoManagerFolderFormGUI: ilRouterGUI
 */
class ilVideoManagerFolderFormGUI extends ilPropertyFormGUI{

    /**
     * @var ilVideoManagerPlugin
     */
    protected $pl;
    /**
     * @var ilTemplate
     */
    protected $tpl;
    /**
     * @var ilCtrl
     */
    protected $ctrl;
    /**
     * @var ilVideoManagerAdminGUI
     */
    protected $parent_gui;

    /**
     * Constructor
     */
    public function __construct($parent_gui, $cmd)
    {
        global $ilCtrl, $tpl;
        parent::__construct();
        $this->parent_gui = $parent_gui;
        $this->ctrl = $ilCtrl;
        $this->pl = ilVideoManagerPlugin::getInstance();
        $this->tpl = $tpl;
        $this->setTitle($this->pl->txt('form_add_folder'));
        $title = new ilTextInputGUI($this->pl->txt('common_title'), 'title');
        $title->setRequired(true);
        $description = new ilTextInputGUI($this->pl->txt('common_description'), 'desc');
        $this->setItems(array($title, $description));
        $this->setValuesByPost();
        $this->ctrl->saveParameterByClass('ilVideoManagerAdminGUI', 'target_id');
        $this->setFormAction($this->ctrl->getFormAction($this->parent_gui));

        switch($cmd)
        {
            case 'create':
                $this->addCommandButton('createFolder', $this->pl->txt('common_add'));
                break;
            case 'edit':
                $this->addCommandButton('saveFolder', $this->pl->txt('common_edit'));
                $this->fillForm();
                break;
        }

        $this->addCommandButton('view', $this->pl->txt('common_cancel'));
    }

    public function createFolder()
    {
        if(!$this->checkInput())
        {
            return false;
        }
        $newObject = new ilVideoManagerObject();
        $newObject->setTitle($_POST['title']);
        $newObject->setDescription($_POST['desc']);
        $newObject->setType('fld');
        $newObject->setCreateDate(date('Y-m-d'));
        $newObject->create();
        ilUtil::sendSuccess($this->pl->txt('msg_fld_created'), true);
        $this->ctrl->setParameterByClass('ilvideomanageradmingui', 'node_id', $newObject->getId());
        $this->ctrl->redirectByClass('ilvideomanageradmingui', 'view');
        return true;
    }

    public function fillForm()
    {
        $folder = new ilVideoManagerObject($_GET['target_id']);
        $array = array(
            'title' => $folder->getTitle(),
            'desc' => $folder->getDescription(),
        );
        $this->setValuesByArray($array);
    }

    public function saveFolder()
    {
        if(!$this->checkInput()){
            return false;
        }

        $object = new ilVideoManagerObject($_GET['target_id']);
        $object->setTitle($this->getInput('title'));
        $object->setDescription($this->getInput('desc'));
        $object->update();

        return true;
    }


} 