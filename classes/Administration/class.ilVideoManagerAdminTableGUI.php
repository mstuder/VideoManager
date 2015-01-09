<?php
require_once('./Services/Table/classes/class.ilTable2GUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerTree.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerObject.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerPlugin.php');


/**
 * Class ilVideoManagerAdminTableGUI
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_IsCalledBy ilVideoManagerAdminTableGUI: ilRouterGUI
 */
class ilVideoManagerAdminTableGUI extends ilTable2GUI{

    /**
     * @var ilCtrl
     */
    protected $ctrl;
    /**
     * @var ilVideoManagerTree
     */
    protected $tree;
    /**
     * @var array
     */
    protected $objects;
    /**
     * @var ilVideoManagerPlugin
     */
    protected $pl;
    /**
     * @var ilTemplate
     */
    protected $tpl;

    protected $available_cols = array(
        'icon',
        'link',
        'actions'
    );

    /**
     * @param int $node_id
     */
    public function __construct($parent_obj, $node_id = 0)
    {
        global $ilCtrl, $tpl;
        $this->ctrl = $ilCtrl;
        $this->tpl = $tpl;
        $this->setId('vidm_admin_tbl_'.$node_id);
        parent::__construct($parent_obj);
        $this->pl = ilVideoManagerPlugin::getInstance();
        $this->tree = new ilVideoManagerTree(1);
        if($node_id == 0)
        {
            $_GET['node_id'] ? $node_id = $_GET['node_id'] : $node_id = ilVideoManagerObject::__getRootFolder()->getId();
        }
        $nodes = $this->tree->getChilds($node_id);

        foreach($nodes as $key => $node)
        {
            $this->objects[] = new ilVideoManagerObject($node['id']);
        }

        $this->setExternalSegmentation(true);
        $this->setRowTemplate('tpl.row_generic.html', $this->pl->getDirectory());

        //TODO: column positioning (e.g. relative positioning -> actions float right
        $this->addColumn('', '', 1);
        $this->addColumn('', '', 800);
        $this->addColumn('', '', 15);
        $this->buildData();
    }

    /**
     * @param array $row
     */
    public function fillRow(array $row){
        foreach($this->available_cols as $col){
            $content = '';
            switch($col){
                case 'icon':
                    $content = '<img src = "' . $row['icon'] . '"> ';
                    break;
                case 'link':
                    $this->ctrl->clearParameters($this->parent_obj);
                    $this->ctrl->setParameter($this->parent_obj, 'node_id', $row['node_id']);
                    $content = '<a href= "' . $this->ctrl->getLinkTarget($this->parent_obj, 'view') . '">' . $row['title'] . '</a>';
                    break;
                case 'actions':
                    $current_selection_list = new ilAdvancedSelectionListGUI();
                    $current_selection_list->setListTitle($this->pl->txt("common_actions"));
                    $current_selection_list->setId($row['node_id']);
                    $this->ctrl->setParameter($this->parent_obj, 'node_id', $_GET['node_id']);
                    $this->ctrl->setParameter($this->parent_obj, 'target_id', $row['node_id']);

                    $current_selection_list->addItem($this->pl->txt("common_delete"), "",
                        $this->ctrl->getLinkTargetByClass('ilvideomanageradmingui', 'confirmDelete'));

                    $current_selection_list->addItem($this->pl->txt("common_edit"), "",
                        $this->ctrl->getLinkTargetByClass('ilvideomanageradmingui', 'edit'));

                    $content = $current_selection_list->getHTML();
                    break;
            }
            $this->tpl->setCurrentBlock('td');
            $this->tpl->setVariable('VALUE', $content);
            $this->tpl->parseCurrentBlock();
        }

    }

    public function buildData()
    {
        $data = array();
        foreach($this->objects as $obj)
        {
            $row = array();

            //icon
            if($obj->getType() == 'fld'){
                $row['icon'] = ilUtil::getImagePath('icon_cat_b.png');
            }else{
                $row['icon'] = ilUtil::getImagePath('icon_mobs_b.png');
            }

            //title
            $row['title'] = $obj->getTitle();
            //type
            $row['type'] = $obj->getType();
            //node id
            $row['node_id'] = $obj->getId();

            $data[] = $row;
        }
        $this->setData($data);
    }



} 