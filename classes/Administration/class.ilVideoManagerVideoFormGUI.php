<?php
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
/**
 * Class ilVideoManagerVideoFormGUI
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class ilVideoManagerVideoFormGUI extends ilPropertyFormGUI{

    /**
     * @var ilVideoManagerAdminGUI
     */
    protected $parent_gui;
    /**
     * @var  ilCtrl
     */
    protected $ctrl;
    /**
     * @var ilVideoManagerObject
     */
    protected $video;


    /**
     * @param              $parent_gui
     */
    public function __construct($parent_gui, ilVideoManagerObject $video) {
        global $ilCtrl;
        $this->parent_gui = $parent_gui;
        $this->video = $video;
        $this->ctrl = $ilCtrl;
        $this->pl = new ilVideoManagerPlugin();
        $this->ctrl->saveParameter($parent_gui, 'vid_id');
        $this->initForm();
    }

    private function initForm() {
        $this->setFormAction($this->ctrl->getFormAction($this->parent_gui));
        if ($this->video->getId() == 0) {
            $this->setTitle($this->pl->txt('form_upload_vid'));
        } else {
            $this->setTitle($this->pl->txt('form_edit_vid'));
        }
        switch ($this->ctrl->getCmd()) {
            case 'edit':
                $title = new ilTextInputGUI($this->pl->txt('vid_title'), 'title');
                $title->setRequired(true);
                $this->addItem($title);
                $desc = new ilTextAreaInputGUI($this->pl->txt('common_description'), 'description');
                $this->addItem($desc);
//                $date_input = new ilDateTimeInputGUI($this->pl->txt('date'), 'create_date');
//                $date_input->setDate(new ilDate($this->picture->getCreateDate(), IL_CAL_DATE));
//                $this->addItem($date_input);
//                $vorschau = new ilCheckboxInputGUI($this->pl->txt('select_preview'), 'vorschau');
//                $vorschau->setValue(1);
//                $this->addItem($vorschau);
                $this->addCommandButton('updateVideo', $this->pl->txt('common_save'));
                $this->addCommandButton('cancel', $this->pl->txt('common_cancel'));
                $this->setFormAction($this->ctrl->getFormActionByClass('ilVideoManagerAdminGUI', 'update'));
                break;
            case 'addVideo':
                $this->setMultipart(true);
                require_once('./Services/Form/classes/class.ilDragDropFileInputGUI.php');
                $file_input = new ilDragDropFileInputGUI($this->pl->txt('form_vid'), 'suffix');
                $file_input->setRequired(true);
                $file_input->setSuffixes(array( '3pgg', 'x-flv', 'mp4', 'webm' ));
                $file_input->setCommandButtonNames('create', 'cancel');
                $this->addItem($file_input);
                $this->addCommandButton('create', $this->pl->txt('common_add'));
                $this->addCommandButton('cancel', $this->pl->txt('common_cancel'));
                $this->setFormAction($this->ctrl->getFormActionByClass('ilVideoManagerAdminGUI', 'create'));
                break;
        }
    }


    public function fillForm() {
        $array = array(
            'title' => $this->video->getTitle(),
            'description' => $this->video->getDescription(),
        );
        $this->setValuesByArray($array);
    }


    /**
     * @description returns whether checkinput was successful or not.
     *
     * @return bool
     */
    public function fillObject() {
        global $ilUser;
        if (! $this->checkInput()) {
            return false;
        }
        $this->video->setTitle($this->getInput('title'));
        $this->video->setDescription($this->getInput('description'));
        $this->video->setType('vid');

//        $this->video->setUserId($ilUser->getId());
//        $date_array = $this->getInput('create_date');
//        $date = $date_array['date']['y'] . '-' . $date_array['date']['m'] . '-' . $date_array['date']['d'];
//        $this->video->setCreateDate($date); // TODO bei MultipleFileUpload Exif-Daten verwenden
//        $this->album->setPreviewId($_GET['picture_id']);

        return true;
    }


    public function saveObject() {
        if (! $this->fillObject()) {
            return false;
        }
        if ($this->video->getId()) {
            if ($_FILES['upload_files']['tmp_name']) {
                $this->video->uploadVideo($_FILES['upload_files']['tmp_name']);
                $ext = strtolower(end(explode('.', $_FILES['upload_files']['name'])));
                $this->video->setSuffix($ext);
            }
            $this->video->update();
        } else {
            $ext = strtolower(end(explode('.', $_FILES['upload_files']['name'])));
            $this->video->setSuffix($ext);

            $this->video->setCreateDate(date('Y-m-d'));

            $this->video->create();
            $this->video->uploadVideo($_FILES['upload_files']['tmp_name']);

            // create answer object
            $response = new stdClass();
            $response->fileName = $_FILES['upload_files']['name'];
            $response->fileSize = intval($_FILES['upload_files']['size']);
            $response->fileType = $_FILES['upload_files']['type'];
            $response->fileUnzipped = '';
            $response->error = NULL;

            return $response;
        }

        return true;
    }

} 