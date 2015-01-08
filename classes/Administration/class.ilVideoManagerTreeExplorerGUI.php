<?php
require_once('./Services/UIComponent/Explorer2/classes/class.ilTreeExplorerGUI.php');
/**
 * Class ilVideoManagerTreeExplorerGUI
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class ilVideoManagerTreeExplorerGUI extends ilTreeExplorerGUI {

    /**
     * Get content of a node
     *
     * @param mixed $a_node node array or object
     * @return string content of the node
     */
    function getNodeContent($node)
    {
        $object = new ilVideoManagerObject($node['id']);
        $icon_src = '';
        switch($object->getType()){
            case 'vid':
                $icon_src = ilUtil::getImagePath('icon_mobs_s.png');
                break;
            case 'fld':
                $icon_src = ilUtil::getImagePath('icon_cat_s.png');
                break;
        }

        if($node["child"] == $_GET["ref_id"])
            return "<img src='".$icon_src."'></img>
                    <span class='ilExp2NodeContent ilHighlighted'> ".$node["title"]."</span>";
        else
            return "<img src='".$icon_src."'></img> ".$node["title"];
    }

    function getNodeHref($node){
        global $ilCtrl;
        if($ilCtrl->getCmd() == "performPaste")
        {
            $ilCtrl->setParameterByClass("ilObjOrgUnitGUI","target_node",$node["child"]);
        }
        $ilCtrl->setParameterByClass("ilVideoManagerAdminGUI", "node_id", $node["child"]);
        return $ilCtrl->getLinkTargetByClass("ilVideoManagerAdminGUI", 'view');
    }



} 