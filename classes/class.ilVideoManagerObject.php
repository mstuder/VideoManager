<?php
require_once('./Customizing/global/plugins/Libraries/ActiveRecord/class.ActiveRecord.php');

/**
 * Class ilVideoManagerObject
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class ilVideoManagerObject extends ActiveRecord{

    const TYPE_FOLDER = 'fld';
    const TYPE_VIDEO = 'vid';

    /**
     * @var int
     *
     * @db_has_field        true
     * @db_is_unique        true
     * @db_is_primary       true
     * @db_is_notnull       true
     * @db_fieldtype        integer
     * @db_length           4
     * @con_sequence        true
     */
    protected $id = 0;
    /**
     * @var String
     *
     * @db_has_field        true
     * @db_is_notnull       true
     * @db_fieldtype        text
     * @db_length           40
     */
    protected $title;
    /**
     * @var string
     *
     * @db_has_field        true
     * @db_fieldtype        text
     * @db_length           256
     */
    protected $description;
    /**
     * @var String
     *
     * @db_has_field        true
     * @db_fieldtype        text
     * @db_length           4
     */
    protected $type = false;
    /**
     * @var bool
     *
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           1
     */
    protected $deleted = false;

    /**
     * @param int $id
     */
    function __construct($id = 0){
        parent::__construct($id);
    }


    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param boolean $deleted
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
    }

    /**
     * @return boolean
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param String $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return String
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param String $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return String
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return ActiveRecord
     */
    public static function __getRootFolder(){
        return parent::where(array('title' => 'Video Manager'))->first();
    }

    /**
     * @return string
     * @description Return the Name of your Database Table
     * @deprecated
     */
    static function returnDbTableName()
    {
        return 'vidm_data';
    }
}