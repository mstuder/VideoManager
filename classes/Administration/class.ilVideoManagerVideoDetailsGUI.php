<?php
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerVideo.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerPlugin.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/Util/class.vmFFmpeg.php');
require_once('./Services/MediaObjects/classes/class.ilObjMediaObject.php');
require_once('./Services/MediaObjects/classes/class.ilMediaItem.php');
require_once('./Customizing/global/plugins/Services/Cron/CronHook/MediaConverter/classes/Media/class.mcMedia.php');



require_once './Customizing/global/plugins/Services/Cron/CronHook/MediaConverter/classes/class.ilMediaConverterResult.php';
require_once './Customizing/global/plugins/Services/Cron/CronHook/MediaConverter/classes/Media/class.mcPid.php';
require_once './Customizing/global/plugins/Services/Cron/CronHook/MediaConverter/classes/Media/class.mcMedia.php';
require_once './Customizing/global/plugins/Services/Cron/CronHook/MediaConverter/classes/Media/class.mcMediaState.php';
require_once './Customizing/global/plugins/Services/Cron/CronHook/MediaConverter/classes/Media/class.mcProcessedMedia.php';
//require_once './Services/MediaObjects/classes/class.ilFFmpeg.php';
require_once './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/Util/class.vmFFmpeg.php';



/**
 * Class ilVideoManagerVideoDetailsGUI
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_IsCalledBy ilVideoManagerVideoDetailsGUI: ilRouterGUI
 */
class ilVideoManagerVideoDetailsGUI {

    /**
     * @var ilVideoManagerAdminGUI
     */
    protected $parent_gui;
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
     * @param $parent_gui
     * @param $video ilVideoManagerVideo
     */
    public function __construct($parent_gui, $video)
    {
        global $tpl, $ilCtrl;
        $this->parent_gui = $parent_gui;
        $this->video = $video;
        $this->ctrl = $ilCtrl;
        $this->pl = ilVideoManagerPlugin::getInstance();
        $this->tpl = $tpl;
    }

    public function init()
    {
        $this->tpl->addBlockFile('ADM_CONTENT', 'video_details', 'tpl.video_details.html', 'Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager');
        $this->tpl->setCurrentBlock('video_details');
        $this->tpl->addCss('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/templates/css/video_details.css');

        $this->initPropertiesForm();
        $this->initMediaPlayer();
    }

    function initPropertiesForm()
    {
        $form = new ilPropertyFormGUI();


        $title = new ilNonEditableValueGUI($this->pl->txt('common_title'));
        $title->setValue($this->video->getTitle());
        $form->addItem($title);

        $description = new ilNonEditableValueGUI($this->pl->txt('common_description'));
        $description->setValue($this->video->getDescription(200));
        $form->addItem($description);

        $tags = new ilNonEditableValueGUI($this->pl->txt('common_tags'));
        $tags->setValue($this->video->getTags());
        $form->addItem($tags);

        $filesize = new ilNonEditableValueGUI($this->pl->txt('common_filesize'));
        $filesize->setValue(number_format(filesize($this->video->getAbsolutePath())) . " Bytes");
        $form->addItem($filesize);

        $this->tpl->setVariable('DESCRIPTION', $form->getHTML());

        return $form;
    }

    function initMediaPlayer()
    {
        require_once('./Services/MediaObjects/classes/class.ilPlayerUtil.php');
        ilPlayerUtil::initMediaElementJs();
//        $mediaObject = new ilObjMediaObject();
//        $mediaObject->setTitle($this->video->getTitle());
//        $mediaObject->create();
//        $mediaItem = new ilMediaItem();
//        $mediaItem->setHeight(300);
//        $mediaItem->setWidth(400);
//        $mediaItem->setMobId($mediaObject->getId());
//        $mediaItem->setPurpose('Standard');
//        $mediaItem->setLocationType('LocalFile');
//        $mediaItem->setLocation($this->video->getAbsolutePath());
//        $mediaItem->setNr(1);
//        $mediaItem->create();
//        $mediaObject->addMediaItem($mediaItem);
//        $mediaObject->uploadVideoPreviewPic($this->video->getPoster());
//        $mediaItem->
//        var_dump($mediaObject->getXML());
//        iljQueryUtil::initjQuery($this->tpl);
//        $this->tpl->addJavaScript('./Customizing/global/plugins/Libraries/mediaelement/build/mediaelement-and-player.min.js');
//        $this->tpl->addCss('./Customizing/global/plugins/Libraries/mediaelement/src/css/mediaelementplayer.css');
        $this->tpl->setVariable('POSTER_SRC', $this->video->getPosterHttp());
        $this->tpl->setVariable('VIDEO_SRC', $this->video->getHttpPath().'/'.$this->video->getTitle());
//        $this->tpl->setVariable('VIDEO', $mediaObject->getXML());

    }

} 