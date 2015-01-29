<?php
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerPlugin.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerVideo.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerFolder.php');
/**
 * Class ilVideoManagerPlayVideoGUI
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 *
 */
class ilVideoManagerPlayVideoGUI {

    /**
     * @var
     */
    protected $parent_gui;
    /**
     * @var ilDB
     */
    protected $db;
    /**
     * @var ilCtrl
     */
    protected $ctrl;
    /**
     * @var ilVideoManagerPlugin
     */
    protected $pl;
    /**
     * @var ilVideoManagerVideo
     */
    protected $video;
    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var array
     */
    protected $options;


    /**
     * @param $parent_gui
     * @param $cmd
     */
    public function __construct($parent_gui){
        global $ilDB, $ilCtrl, $tpl;
        $this->db = $ilDB;
        $this->ctrl = $ilCtrl;
        $this->pl = ilVideoManagerPlugin::getInstance();
        $this->tpl = $tpl;
        $this->video = new ilVideoManagerVideo($_GET['node_id']);
    }

    public function init()
    {
        $this->tpl->addBlockFile('ADM_CONTENT', 'video_player', 'tpl.video_player.html', 'Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager');
        $this->tpl->addJavaScript('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/templates/js/video_player.js');
        $this->tpl->setCurrentBlock('video_player');
        $this->initMediaPlayer();
        $this->initRelatedVideosTable();
        $this->initDescription();
    }

    function initMediaPlayer()
    {
        require_once('./Services/MediaObjects/classes/class.ilPlayerUtil.php');
        ilPlayerUtil::initMediaElementJs();
        $this->tpl->setVariable('POSTER_SRC', $this->video->getPosterHttp());
        $this->tpl->setVariable('VIDEO_SRC', $this->video->getHttpPath().'/'.$this->video->getTitle());
    }

    function initRelatedVideosTable()
    {
        $options = array(
            'cmd' => 'related_videos',
            'search' => array(
                'method' => 'related'
            ),
            'limit' => 10
        );
        $related_vids = new ilVideoManagerVideoTableGUI($this, $options, $this->video);
        $this->tpl->setVariable('RELATED_VIDEOS_TABLE', $related_vids->getHTML());
    }

    function initDescription()
    {
        $this->tpl->setVariable('TITLE', $this->video->getTitle());
        if($this->video->getDescription() && strlen($this->video->getDescription()) > 350){
            $this->tpl->setVariable('DESCRIPTION', $this->video->getDescription());
            $this->tpl->setVariable('DESCRIPTION_SHORT', $this->video->getDescription(350));
            $this->tpl->setVariable('MORE', '['.$this->pl->txt('common_more').']');
            $this->tpl->setVariable('LESS', '['.$this->pl->txt('common_less').']');
        }elseif($this->video->getDescription()){
            $this->tpl->setVariable('DESCRIPTION_SHORT', $this->video->getDescription());
        }
        if($tags = $this->video->getTags())
        {
            $this->tpl->setVariable('TAGS_KEY', 'Tags: ');
            foreach(explode(' ', $this->video->getTags()) as $tag)
            {
                $this->tpl->setCurrentBlock('tags');
                $this->ctrl->setParameterByClass('ilVideoManagerUserGUI', 'node_id', $_GET['node_id']);
                $this->ctrl->setParameterByClass('ilVideoManagerUserGUI', 'search_value', $tag);
                $this->ctrl->setParameterByClass('ilVideoManagerUserGUI', 'search_method', 'tag');
                $this->tpl->setVariable('TAG_SEARCH', $this->ctrl->getLinkTargetByClass('ilVideoManagerUserGUI', 'performSearch'));
                $this->tpl->setVariable('TAGS_VALUE', $tag);
                $this->tpl->parseCurrentBlock();
            }
        }

        $tree = new ilVideoManagerTree(1);
        $category = new ilVideoManagerFolder($tree->getParentId($this->video->getId()));
        $this->tpl->setVariable('CATEGORY_KEY', 'Category: ');
        $this->tpl->setVariable('CATEGORY_VALUE', $category->getTitle());

        $this->ctrl->setParameterByClass('ilVideoManagerUserGUI', 'node_id', $_GET['node_id']);
        $this->ctrl->setParameterByClass('ilVideoManagerUserGUI', 'search_value', $category->getTitle());
        $this->ctrl->setParameterByClass('ilVideoManagerUserGUI', 'search_method', 'category');
        $this->tpl->setVariable('CATEGORY_SEARCH', $this->ctrl->getLinkTargetByClass('ilVideoManagerUserGUI', 'performSearch'));
    }
} 