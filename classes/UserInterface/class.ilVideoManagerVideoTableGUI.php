<?php
require_once('./Services/Table/classes/class.ilTable2GUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerPlugin.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerVideo.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerTree.php');

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
    /**
     * @var int
     */
    protected $max_desc_length;

    /**
     * @param $parent_gui
     * @param array $options
     * @param ilVideoManagerVideo $video
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
        $this->setId('video_tbl');
        $this->setDefaultOrderField('create_date');
        $this->setShowRowsSelector(false);
        $this->setFormAction($ilCtrl->getFormAction($parent_gui));
        $this->setEnableNumInfo(true);

        if($options['cmd'] == 'related_videos'){
            $this->max_desc_length = 70;
            $this->setExternalSegmentation(true);
            $this->options['limit'] = 10;
        }else{
            $this->max_desc_length = 320;
        }

        $this->setRowTemplate('tpl.video_tbl_row.html', $this->pl->getDirectory());

        $this->addColumn('', '');
        $this->addColumn('', '');
        $this->buildData();
    }

    public function buildData(){
        $data = $this->createData();
        $this->setData($data);
    }

    public function fillRow($row)
    {
        //first row with id 0 is the title
        if($row['id'] == 0)
        {
            $this->tpl->setCurrentBlock('tbl_title');
            $this->tpl->setVariable('ID', 0);
            $this->tpl->setVariable('TBL_TITLE', $this->pl->txt('tbl_' . $this->options['cmd']));
            $this->tpl->parseCurrentBlock();
        }else{
            //all other rows
            $this->tpl->setCurrentBlock('td');
            $this->tpl->setVariable('ID', $row['id']);
            $this->tpl->setVariable('IMAGE', $row['img']);
            $this->tpl->setVariable('LINK', $row['link']);
            $this->tpl->setVariable('TITLE', $row['title']);
            $this->tpl->setVariable('DESCRIPTION', $row['description']);
            $this->tpl->parseCurrentBlock();
        }
    }

    public function createData()
    {
        if($this->options['count'])
        {
            $sql = 'SELECT COUNT(vidm_data.id) AS count';
        }else{
            $sql = 'SELECT *';
        }

        $sql .= ' FROM vidm_data
                    JOIN vidm_tree ON (vidm_tree.child = vidm_data.id)';

        $sql .= ' WHERE vidm_data.type = ' . $this->db->quote('vid', 'text');

        foreach($this->options as $option => $value)
        {
            switch($option)
            {

                case 'search':
                    switch($value['method'])
                    {
                        case '':
                        case 'all':
                            $sql .= ' AND (';
                            $or = '';
                            if(!is_array($value['value'])){
                                $value['value'] = array($value['value']);
                            }
                            foreach($value['value'] as $word)
                            {
                                $sql .= $or;
                                $sql .= 'vidm_data.title LIKE ' . $this->db->quote("%" . $word . "%", 'text');
                                $sql .= ' OR vidm_data.description LIKE ' . $this->db->quote("%" . $word . "%", 'text');
                                $sql .= ' OR vidm_data.tags LIKE ' . $this->db->quote("%" . $word . "%", 'text');
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
                            $sql .= ' AND vidm_tree.parent = ' . $value['value'];
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
        $query = $this->db->query($sql);
        if ($this->options['count'])
        {
            return (int)$this->db->fetchObject($query)->count;
        }

        $data = array();
        $data[] = array('id' => 0);
        while($result = $this->db->fetchAssoc($query))
        {
            $row = array();
            $video = new ilVideoManagerVideo($result['id']);
            $row['img'] = $video->getPreviewImageHttp();
            $row['title'] = $video->getTitle();
            $row['id'] = $video->getId();
            $row['create_date'] = $video->getCreateDate();
            $this->ctrl->setParameterByClass('ilvideomanagerusergui', 'node_id', $video->getId());
            $row['link'] = $this->ctrl->getLinkTargetByClass('ilvideomanagerusergui', 'playVideo');
            $row['description'] = $video->getDescription($this->max_desc_length);

            $data[] = $row;
        }

        return $data;
    }





} 