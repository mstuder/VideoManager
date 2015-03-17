<?php
require_once('class.videoman.php');
videoman::loadActiveRecord();

/**
 * Class ilVideoManagerSubscription
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class ilVideoManagerSubscription extends ActiveRecord {

	/**
	 * @var int
	 *
	 * @db_has_field        true
	 * @db_is_primary       true
	 * @db_is_notnull       true
	 * @db_fieldtype        integer
	 * @db_length           4
	 * @con_sequence        true
	 */
	protected $id;
	/**
	 * @var int
	 *
	 * @db_has_field        true
	 * @db_is_notnull       true
	 * @db_fieldtype        integer
	 * @db_length           4
	 */
	protected $cat_id;
	/**
	 * @var int
	 *
	 * @db_has_field        true
	 * @db_is_notnull       true
	 * @db_fieldtype        integer
	 * @db_length           4
	 */
	protected $usr_id;


	/**
	 * @param int $id
	 */
	public function setId($id) {
		$this->id = $id;
	}


	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}


	/**
	 * @param int $fld_id
	 */
	public function setCatId($cat_id) {
		$this->cat_id = $cat_id;
	}


	/**
	 * @return int
	 */
	public function getCatId() {
		return $this->cat_id;
	}


	/**
	 * @param int $usr_id
	 */
	public function setUsrId($usr_id) {
		$this->usr_id = $usr_id;
	}


	/**
	 * @return int
	 */
	public function getUsrId() {
		return $this->usr_id;
	}


	public static function isSubscribed($usr_id, $cat_id) {
		return (bool)ilVideoManagerSubscription::where(array( 'usr_id' => $usr_id, 'cat_id' => $cat_id ))->first();
	}


	/**
	 * @return string
	 * @description Return the Name of your Database Table
	 * @deprecated
	 */
	static function returnDbTableName() {
		return "vidm_subscription";
	}
}