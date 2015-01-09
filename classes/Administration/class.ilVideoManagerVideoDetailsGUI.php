<?php
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerVideo.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerPlugin.php');


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
        $form = $this->initPropertiesForm();
        $mp = $this->initMediaPlayer();
        ilFFmpeg::convert($this->video->getAbsolutePath(), 'video/webm');

//        ilFFmpeg::extractImage($this->video->getAbsolutePath(), $this->video->getTitle().'_poster.png', $this->video->getPath());
//        file($this->video->getPreviewImage());
//        ilUtil::resizeImage($this->video->getPoster(), $this->video->getPreviewImage(), 120, 80, true);

        $this->tpl->setContent($mp.$form->getHTML());
    }

    function initPropertiesForm()
    {
        $form = new ilPropertyFormGUI();
        $form->setTableWidth(640);      //funktioniert nicht


        $title = new ilNonEditableValueGUI($this->pl->txt('common_title'));
        $title->setValue($this->video->getTitle());
        $form->addItem($title);

        $description = new ilNonEditableValueGUI($this->pl->txt('common_description'));
        $description->setValue($this->video->getDescription());
        $form->addItem($description);

        $tags = new ilNonEditableValueGUI($this->pl->txt('common_tags'));
        $tags->setValue($this->video->getTags());
        $form->addItem($tags);

        $filesize = new ilNonEditableValueGUI($this->pl->txt('common_filesize'));
        $filesize->setValue(number_format(filesize($this->video->getAbsolutePath())) . " Bytes");
        $form->addItem($filesize);

        return $form;
    }

    function initMediaPlayer()
    {
        iljQueryUtil::initjQuery($this->tpl);
        $this->tpl->addJavaScript('./Customizing/global/plugins/Libraries/mediaelement/build/mediaelement-and-player.min.js');
        $this->tpl->addCss('./Customizing/global/plugins/Libraries/mediaelement/src/css/mediaelementplayer.css');
        $mp = '<video class="mejs-player" poster = "' . $this->video->getPosterHttp() .'" src="' . $this->video->getAbsoluteHttpPath() . '" width="640" height="360"></video>';
        return $mp;
    }

} 