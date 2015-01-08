<?php

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
     * @var ilVideoManagerObject
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
     * @param $video ilVideoManagerObject
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
//        $this->tpl->setLeftContent($form->getHTML());
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
        $mp = '<video class="mejs-player" src="' . $this->video->getAbsoluteHttpPath() . '" width="640" height="360"></video>';
        return $mp;
    }

} 