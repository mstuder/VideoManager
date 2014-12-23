<#1>
<?php
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerObject.php');
ilVideoManagerObject::installDB();
?>
<#2>
<?php
global $ilDB;
$ilDB->insert('vidm_data', array(
        'id' => array('integer', $ilDB->nextId('vidm_data')),
        'title' => array('text', 'Video Manager'),
        'description' => array('text', ''),
        'type' => array('text', 'fld'),
        'deleted' => array('integer', 0),
));
?>
<#3>
<?php
if(!$ilDB->tableExists('vidm_tree'))
{
    $fields = array(
        'tree' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'child' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'parent' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => false,
        ),
        'lft' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'rgt' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'depth' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        )
    );
    $ilDB->createTable('vidm_tree', $fields);
}
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerTree.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerObject.php');
$tree = new ilVideoManagerTree(1);
$tree->addTree($tree->getTreeId());
?>