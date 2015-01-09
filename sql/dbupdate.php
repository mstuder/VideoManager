<#1>
<?php
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerObject.php');
ilVideoManagerObject::installDB();
?>
<#2>
<?php
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerFolder.php');
if($root_folder = ilVideoManagerFolder::__getRootFolder())
{
    $root_folder->setTitle('Video Manager');
    $root_folder->update();
}else{
    $root_folder = new ilVideoManagerFolder();
    $root_folder->setId(1);
    $root_folder->setTitle('Video Manager');
    $root_folder->create();
}
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