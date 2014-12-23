<?php

/**
 * Class ilObjVideoManagerTree
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class ilVideoManagerTree extends ilTree{

    /**
     * @var ilVideoManagerTree
     */
    protected static $instance;


    /**
     * Constructor
     *
     * @param int $tree_id
     */
    function __construct($tree_id)
    {
        parent::__construct($tree_id);
        $this->setTableNames('vidm_tree','vidm_data');
        $this->setObjectTablePK('obj_id');
        $this->setTreeTablePK('tree');
        $this->setRootId(ilVideoManagerObject::__getRootFolder());
    }
//
//    /**
//     * @return ilVideoManagerTree
//     */
//    public static function getInstance() {
//        if (!isset(self::$instance)) {
//            self::$instance = new self(1);
//        }
//
//        return self::$instance;
//    }
}