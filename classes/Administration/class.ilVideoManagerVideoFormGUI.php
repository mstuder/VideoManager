<?php
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerVideo.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerPlugin.php');
require_once('./Customizing/global/plugins/Services/Cron/CronHook/MediaConverter/classes/Media/class.mcMedia.php');
require_once('./Services/MediaObjects/classes/class.ilFFmpeg.php');


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
     * @var ilVideoManagerVideo
     */
    protected $video;
    /**
     * @var ilVideoManagerPlugin
     */
    protected $pl;

    /**
     * @param $parent_gui
     * @param ilVideoManagerVideo $video
     */
    public function __construct($parent_gui, ilVideoManagerVideo $video) {
        global $ilCtrl;
        $this->parent_gui = $parent_gui;
        $this->video = $video;
        $this->ctrl = $ilCtrl;
        $this->pl = new ilVideoManagerPlugin();
        $this->initForm();
    }

    private function initForm() {
        $this->setFormAction($this->ctrl->getFormAction($this->parent_gui));

        switch ($this->ctrl->getCmd())
        {
            case 'editvid':
                $this->setTitle($this->pl->txt('form_edit_vid'));

                $title = new ilTextInputGUI($this->pl->txt('common_title'), 'title');
                $title->setRequired(true);
                $this->addItem($title);

                $desc = new ilTextAreaInputGUI($this->pl->txt('common_description'), 'description');
                $this->addItem($desc);

                $tags = new ilTextInputGUI($this->pl->txt('form_tags'), 'tags');
                $this->addItem($tags);

                $this->addCommandButton('updateVideo', $this->pl->txt('common_save'));
                $this->addCommandButton('cancel', $this->pl->txt('common_cancel'));

                $this->ctrl->saveParameterByClass('ilvideomanageradmingui', 'target_id');
                $this->setFormAction($this->ctrl->getFormActionByClass('ilVideoManagerAdminGUI', 'update'));

                break;

            case 'addVideo':
                $this->setTitle($this->pl->txt('form_upload_vid'));
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

    public function fillForm()
    {
        $array = array(
            'title' => $this->video->getTitle(),
            'description' => $this->video->getDescription(),
            'tags' => $this->video->getTags(),
            'suffix' => $this->video->getSuffix(),
        );
        $this->setValuesByArray($array);
    }

    /**
     * @description returns whether checkinput was successful or not.
     *
     * @return bool
     */
    public function fillObject()
    {
        if (!$this->checkInput()) {
            return false;
        }
        $this->video->setTitle(reset(explode('.', $this->getInput('title'))));
        $this->video->setDescription($this->getInput('description'));
        $this->video->setTags($this->getInput('tags'));

        return true;
    }


    public function saveObject()
    {
        if (! $this->fillObject()) {
            return false;
        }

        if ($this->video->getId())
        {
            //rename each file in the directory
            $dir = scandir($this->video->getPath());
            foreach($dir as $file)
            {
                $suffix = array_pop(explode('.', $file));

                $ending = '';
                if(preg_match('/[.]*_poster[.]*/', $file))
                {
                    $ending = '_poster';

                }
                elseif(preg_match('/[.]*_preview[.]*/', $file))
                {
                    $ending = '_preview';

                }
                rename($this->video->getPath().'/'.$file, $this->video->getPath().'/'.$this->video->getTitle().$ending.'.'.$suffix);
            }
            $this->video->update();
            $this->ctrl->redirect($this->parent_gui, 'view');
        }
        else
        {
            $ext = strtolower(end(explode('.', $_FILES['upload_files']['name'])));
            $this->video->setSuffix($ext);
            $this->video->setCreateDate(date('Y-m-d'));
            $this->video->create();
            $this->video->uploadVideo($_FILES['upload_files']['tmp_name']);

            $this->parent_gui->notifyUsers($this->video);

            $mediaConverter = new mcMedia();
            $mediaConverter->uploadFile($this->video->getTitle(), $this->video->getSuffix(), $this->video->getPath(), $this->video->getPath(), $this->video->getId());

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