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
     * @var string
     *
     * @db_has_field        true
     * @db_fieldtype        text
     * @db_length           8
     */
    protected $suffix;
    /**
     * @var int
     *
     * @db_has_field        true
     * @db_fieldtype        date
     * @db_length           4
     */
    protected $create_date;
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
     * @param String $suffix
     */
    public function setSuffix($suffix)
    {
        $this->suffix = $suffix;
    }

    /**
     * @return String
     */
    public function getSuffix()
    {
        return $this->suffix;
    }

    /**
     * @param int $create_date
     */
    public function setCreateDate($create_date)
    {
        $this->create_date = $create_date;
    }

    /**
     * @return int
     */
    public function getCreateDate()
    {
        return $this->create_date;
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

    /**
     * the id of this objects parent has to be stored in $_GET['node_id'] to create it properly
     */
    public function create()
    {
        parent::create();

        if($_GET['node_id'])
        {
            $tree = new ilVideoManagerTree(1);
            $tree->insertNode($this->getId(), $_GET['node_id']);
            $this->recursiveMkdir($this->getPath());
        }

    }

    /**
     * @return string f.e. for localhost: 'http://localhost/ilias_44/data/client_id/mobs/vidm/1/2/5/video_6
     */
    public function getHttpPath() {
        $path = $this->getTreePath();
        $this->type == 'vid' ? $video_prefix = 'video_' : $video_prefix = '';
        return ilUtil::_getHttpPath().'/data/' . CLIENT_ID . '/mobs/vidm' . $path . '/' . $video_prefix . $this->getId();
    }

    /**
     * @return string absolute http path
     */
    public function getAbsoluteHttpPath()
    {
        return $this->getHttpPath().'/'.$this->getTitle();
    }

    /**
     * @return string absolute path
     */
    public function getAbsolutePath()
    {
        return $this->getPath().'/'.$this->getTitle();
    }


    /**
     * @return string f.e.: '/var/www/ilias_44/data/client_id/mobs/vidm/1/2/5/video_6'
     */
    public function getPath() {
        $path = $this->getTreePath();
        $this->type == 'vid' ? $video_prefix = 'video_' : $video_prefix = '';
        return ILIAS_ABSOLUTE_PATH.'/'.ILIAS_WEB_DIR.'/'.CLIENT_ID.'/mobs/vidm' . $path . '/'. $video_prefix . $this->getId();
    }


    /**
     * @param string $tmp_path
     *
     * @return bool
     */
    public function uploadVideo($tmp_path) {
        if($this->getType() != 'vid'){
            return false;
        }

        move_uploaded_file($tmp_path, $this->getPath().'/'.$this->getTitle());

        return true;
    }


    /**
     * @param $path
     *
     * @return bool
     */
    protected function recursiveMkdir($path) {
        $dirs = explode(DIRECTORY_SEPARATOR, $path);
        $count = count($dirs);
        $path = '';
        for ($i = 0; $i < $count; ++$i) {
            if ($path != '/') {
                $path .= DIRECTORY_SEPARATOR . $dirs[$i];
            } else {
                $path .= $dirs[$i];
            }
            if (! is_dir($path)) {
                ilUtil::makeDir(($path));
            }
        }

        return true;
    }


    public function delete() {
        parent::delete();
        ilUtil::delDir($this->getPath());
    }

    /**
     * @return string
     */
    protected function getTreePath()
    {
        $tree = new ilVideoManagerTree(1);
        $path = '';
        foreach ($tree->getPathFull($tree->getParentId($this->getId()), ilVideoManagerObject::__getRootFolder()->getId()) as $node) {
            $path .= '/' . $node['id'];
        }
        return $path;
    }
}