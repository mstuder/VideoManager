<?php
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerVideo.php');
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once('./Services/Form/classes/class.ilNonEditableValueGUI.php');

/**
 * Class ilVideoManagerVideoDetailsGUI
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_IsCalledBy ilVideoManagerVideoDetailsGUI: ilRouterGUI
 */
class ilVideoManagerVideoDetailsGUI {

    /**
     * @var ilVideoManagerVideo
     */
    protected $video;
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
     * @param $video ilVideoManagerVideo
     */
    public function __construct($parent_gui, $video)
    {
        global $tpl;
        $this->parent_gui = $parent_gui;
        $this->video = $video;
        $this->pl = ilVideoManagerPlugin::getInstance();
        $this->tpl = $tpl;
    }

    public function init()
    {
        if(!ilVideoManagerObject::__checkConverting($this->video->getId()))
        {
            ilUtil::sendInfo($this->pl->txt('msg_vid_converting'), true);
        }
        $this->tpl->addBlockFile('ADM_CONTENT', 'video_details', 'tpl.video_details.html', 'Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager');
        $this->tpl->setCurrentBlock('video_details');
        $this->tpl->addCss('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/templates/css/video_details.css');
        $this->initPropertiesForm();
        $this->initMediaPlayer();
    }

    function initPropertiesForm()
    {
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->video->getTitle());
        //Title
        $title = new ilNonEditableValueGUI($this->pl->txt('common_title'));
        $title->setValue($this->video->getTitle());
        $form->addItem($title);

        //Description
        $description = new ilNonEditableValueGUI($this->pl->txt('common_description'));
        $description->setValue($this->video->getDescription(200));
        if(!$description->getValue())
        {
            $description->setValue('-');
        }
        $form->addItem($description);

        //Tags
        $tags = new ilNonEditableValueGUI($this->pl->txt('common_tags'));
        $tags->setValue($this->video->getTags());
        if(!$tags->getValue())
        {
            $tags->setValue('-');
        }
        $form->addItem($tags);

        //Duration
        $duration = new ilNonEditableValueGUI($this->pl->txt('common_duration'));
        $duration->setValue(vmFFmpeg::getDuration($this->video->getAbsolutePath(), false));
        $form->addItem($duration);

        //Filesize
        $filesize = new ilNonEditableValueGUI($this->pl->txt('common_filesize'));
        if(filesize($this->video->getAbsolutePath()) < 1000)
        {
            $filesize->setValue(number_format(filesize($this->video->getAbsolutePath()), 1, '.', "'") . " Bytes");
        }
        elseif(filesize($this->video->getAbsolutePath()) < 1000000)
        {
            $filesize->setValue(number_format(filesize($this->video->getAbsolutePath())/1000, 1, '.', "'") . " KB");
            $form->addItem($filesize);
        }
        else
        {
            $filesize->setValue(number_format(filesize($this->video->getAbsolutePath())/1000000, 1, '.', "'") . " MB");
            $form->addItem($filesize);
        }

        //Upload Date
        $update = new ilNonEditableValueGUI($this->pl->txt('common_upload_date'));
        $update->setValue($this->video->getCreateDate());
        $form->addItem($update);

        //TODO add more

        $this->tpl->setVariable('DESCRIPTION', $form->getHTML());
    }

    function initMediaPlayer()
    {
        require_once('./Services/MediaObjects/classes/class.ilPlayerUtil.php');
        ilPlayerUtil::initMediaElementJs();
        $this->tpl->setVariable('POSTER_SRC', $this->video->getPosterHttp());
        $this->tpl->setVariable('VIDEO_SRC', $this->video->getHttpPath().'/'.$this->video->getTitle());
    }

} 