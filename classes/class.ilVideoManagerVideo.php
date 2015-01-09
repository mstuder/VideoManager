<?php
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerObject.php');
require_once('./Services/MediaObjects/classes/class.ilFFmpeg.php');
/**
 * Class ilVideoManagerVideo
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class ilVideoManagerVideo extends ilVideoManagerObject{

    public function __construct($id = 0)
    {
        $this->type = 'vid';
        parent::__construct($id);
    }

    public function create()
    {
        parent::create();
        ilUtil::convertImage($this->getAbsolutePath(), $this->getImagePath(), "jpeg", "80");
    }

    /**
     * @param string $tmp_path
     *
     * @return bool
     */
    public function uploadVideo($tmp_path) {
        move_uploaded_file($tmp_path, $this->getPath().'/'.$this->getTitle().'.'.$this->getSuffix());

        return true;
    }

    public function getImagePath()
    {
        return $this->getPath().'/'.rtrim($this->getTitle(), '.'.$this->getSuffix()).'_poster';
    }

} 