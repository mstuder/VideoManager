<?php
require_once('./Services/UIComponent/Explorer2/classes/class.ilTreeExplorerGUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerObject.php');

/**
 * Class ilVideoManagerTreeExplorerGUI
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class ilVideoManagerTreeExplorerGUI extends ilTreeExplorerGUI {

    /**
     * @var array
     */
    protected $ignoreSubTree;

    /**
     * Get content of a node
     *
     * @param mixed $a_node node array or object
     * @return string content of the node
     */
    function getNodeContent($node)
    {
        $object = new ilVideoManagerObject($node['id']);
        $icon_src = $object->getIcon(true);

        if($node["child"] == $_GET["ref_id"])
            return "<img src='".$icon_src."'></img>
                    <span class='ilExp2NodeContent ilHighlighted'> ".$node["title"]."</span>";
        else
            return "<img src='".$icon_src."'></img> ".$node["title"];
    }

    function getNodeHref($node){
        global $ilCtrl;
        if($ilCtrl->getCmd() == "cut" || $ilCtrl->getCmd() == "moveMultiple")
        {
            $ilCtrl->saveParameterByClass("ilVideoManagerAdminGUI", "target_id");
            $ilCtrl->setParameterByClass("ilVideoManagerAdminGUI","node_id",$node["child"]);
            return $ilCtrl->getLinkTargetByClass("ilVideoManagerAdminGUI", 'performPaste');
        }
        $ilCtrl->setParameterByClass("ilVideoManagerAdminGUI", "node_id", $node["child"]);
        return $ilCtrl->getLinkTargetByClass("ilVideoManagerAdminGUI", 'view');
    }

    /**
     * Preload childs
     */
    protected function preloadChilds()
    {
        $subtree = $this->tree->getSubTree($this->getRootNode());
        foreach ($subtree as $s)
        {
            $wl = $this->getTypeWhiteList();
            if (is_array($wl) && count($wl) > 0 && !in_array($s["type"], $wl))
            {
                continue;
            }
            $bl = $this->getTypeBlackList();
            if (is_array($bl) && count($bl) > 0 && in_array($s["type"], $bl))
            {
                continue;
            }
            if (is_array($this->ignoreSubTree) && in_array($s, $this->ignoreSubTree))
            {
                continue;
            }
            $this->childs[$s["parent"]][] = $s;
            $this->all_childs[$s["child"]] = $s;
        }

        if ($this->order_field != "")
        {
            foreach ($this->childs as $k => $childs)
            {
                $this->childs[$k] = ilUtil::sortArray($childs, $this->order_field, "asc", $this->order_field_numeric);
            }
        }

        // sort childs and store prev/next reference
        if ($this->order_field == "")
        {
            $this->all_childs =
                ilUtil::sortArray($this->all_childs, "lft", "asc", true, true);
            $prev = false;
            foreach ($this->all_childs as $k => $c)
            {
                if ($prev)
                {
                    $this->all_childs[$prev]["next_node_id"] = $k;
                }
                $this->all_childs[$k]["prev_node_id"] = $prev;
                $this->all_childs[$k]["next_node_id"] = false;
                $prev = $k;
            }
        }

        $this->preloaded = true;
    }

    public function setIgnoreSubTree(array $subtree)
    {
        $this->ignoreSubTree = $subtree;
        $this->preloadChilds();
    }


} 