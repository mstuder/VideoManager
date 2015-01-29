<?php
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerObject.php');
/**
 * Class ilVideoManagerFolder
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class ilVideoManagerFolder extends ilVideoManagerObject{

    public function __construct($id = 0)
    {
        $this->type = 'fld';
        parent::__construct($id);
    }



} 