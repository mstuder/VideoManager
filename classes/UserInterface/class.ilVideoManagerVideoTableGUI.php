<?php
require_once('./Services/Table/classes/class.ilTable2GUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerPlugin.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerVideo.php');

/**
 * Class ilVideoManagerVideoTableGUI
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_IsCalledBy ilVideoManagerVideoTableGUI: ilRouterGUI
 */
class ilVideoManagerVideoTableGUI extends ilTable2GUI{
    /**
     * @var
     */
    protected $parent_gui;
    /**
     * @var String
     */
    protected $cmd;
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
     * @var ilVideoManagerTree
     */
    protected $tree;

    /**
     * @var array
     */
    protected $options;

    protected $available_cols = array(
        'img',
        'link',
    );

    /**
     * @param $parent_gui
     * @param $cmd
     */
    public function __construct($parent_gui, $options, ilVideoManagerVideo $video = NULL){
        global $ilDB, $ilCtrl;
        parent::__construct($parent_gui, $options['cmd']);
        $this->db = $ilDB;
        $this->ctrl = $ilCtrl;
        $this->tree = new ilVideoManagerTree(1);
        $this->pl = ilVideoManagerPlugin::getInstance();
        $this->video = $video;
        $this->options = $options;
        $this->setId($options['cmd'].'_tbl');

        switch($options['cmd'])
        {
            case 'search_results':
                $this->setTitle($this->pl->txt('tbl_'.$options['cmd']));
                break;
            case 'latest_uploads':
                $this->setEnableNumInfo(false);
                $this->setTitle($this->pl->txt('tbl_'.$options['cmd']));
                break;
            case 'related_videos':
                $this->setEnableNumInfo(false);
                break;
        }

        $this->setRowTemplate('tpl.video_tbl_row.html', $this->pl->getDirectory());

        $this->addColumn('', '', 5);
        $this->addColumn('', '', 800);
        $this->buildData();
    }

    public function fillRow($row)
    {
        foreach($this->available_cols as $col){
            $content = '';
            switch($col){
                case 'img':
                    $content = '<a href="' . $row['link'] . '"><img src =

                    "' . $row['img'] . '"></a> ';
                    break;
                case 'link':
                    $content = '<a href="' . $row['link'] . '">' . $row['title'] . '</a>' . '<br>'.$row['description'];
                    break;
            }
            $this->tpl->setCurrentBlock('td');
            $this->tpl->setVariable('VALUE', $content);
            $this->tpl->parseCurrentBlock();
        }
    }

    public function buildData()
    {
        $sql = 'SELECT *
                    FROM vidm_data
                    JOIN vidm_tree ON (vidm_tree.child = vidm_data.id)';

        $sql .= ' WHERE vidm_data.type = ' . $this->db->quote('vid', 'text');

        foreach($this->options as $option => $value)
        {
            switch($option)
            {

                case 'search':
                    switch($value['method'])
                    {
                        case 'all':
                            $sql .= ' AND (';
                            $or = '';
                            if(!is_array($value['value'])){
                                $value['value'] = array($value['value']);
                            }
                            foreach($value['value'] as $word)
                            {
                                $sql .= $or;
                                $sql .= 'vidm_data.title LIKE ' . $this->db->quote($word . "%", 'text');
                                $sql .= ' OR vidm_data.description LIKE ' . $this->db->quote($word . "%", 'text');
                                $sql .= ' OR vidm_data.tags LIKE ' . $this->db->quote($word . "%", 'text');
                                $or = ' OR ';
                            }
                            $sql .= ')';
                            break;
                        case 'related':
                            //related videos search for same tags/categories
                            $tree = new ilVideoManagerTree(1);
                            $sql .= ' AND (vidm_tree.parent = ' . $tree->getParentId($this->video->getId()); //categories names must be unique

                            if($this->video->getTags()){
                                foreach(explode(' ', $this->video->getTags()) as $tag){
                                    $sql .= ' OR vidm_data.tags LIKE ' . $this->db->quote("%" . $tag . "%", 'text');
                                }
                            }
                            $sql .= ')';
                            $sql .= ' AND vidm_data.id != ' . $this->video->getId();
                            break;
                        case 'category':
                            $sql .= ' AND vidm_tree.parent = ' . ilVideoManagerFolder::where(array('title' => $value['value']))->first()->getId(); //categories names must be unique
                            break;
                        case 'tag':
                            $sql .= ' AND vidm_data.tags LIKE ' . $this->db->quote("%" . $value['value'] . "%", 'text');
                            break;
                    }
                    break;

                case 'sort_create_date':
                    $sql .= ' ORDER BY vidm_data.create_date ' . $value;
                    break;

                case 'limit':
                    $sql .= ' LIMIT ' . $value;
                    break;
            }
        }
var_dump($sql);
        $query = $this->db->query($sql);
        $data = array();

        while($result = $this->db->fetchAssoc($query))
        {
            $row = array();
            $video = new ilVideoManagerVideo($result['id']);
            $row['img'] = $video->getPreviewImageHttp();
            $row['title'] = $video->getTitle();
            $this->ctrl->setParameterByClass('ilvideomanagerusergui', 'node_id', $video->getId());
            $row['link'] = $this->ctrl->getLinkTargetByClass('ilvideomanagerusergui', 'playVideo');
            $row['description'] = $video->getDescription();

            $data[] = $row;
        }

        $this->setData($data);
    }





} 