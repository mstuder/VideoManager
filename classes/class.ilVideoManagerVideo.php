<?php
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerObject.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/Util/class.vmFFmpeg.php');
require_once('./Customizing/global/plugins/Services/Cron/CronHook/MediaConverter/classes/Media/class.mcMedia.php');
require_once('./Services/MediaObjects/classes/class.ilFFmpeg.php');

/**
 * Class ilVideoManagerVideo
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class ilVideoManagerVideo extends ilVideoManagerObject {

	/**
	 * @var int
	 */
	protected $MCId;


	/**
	 * @param int $id
	 */
	public function __construct($id = 0) {
		$this->type = 'vid';
		parent::__construct($id);
	}


	/**
	 * @param string $tmp_path
	 *
	 * @return bool
	 */
	public function uploadVideo($tmp_path) {
		move_uploaded_file($tmp_path, $this->getPath() . '/' . $this->getTitle() . '.' . $this->getSuffix());
		vmFFmpeg::extractImage($this->getAbsolutePath(), $this->getTitle()
			. '_poster.png', $this->getPath(), (vmFFmpeg::getDuration($this->getAbsolutePath()) / 3));
		ilUtil::resizeImage($this->getPoster(), $this->getPreviewImage(), 178, 100, true);

		return true;
	}


	/**
	 * @return string
	 */
	public function getPreviewImage() {
		return $this->getPath() . '/' . $this->getTitle() . '_preview.png';
	}


	/**
	 * @return string
	 */
	public function getPoster() {
		return $this->getPath() . '/' . $this->getTitle() . '_poster.png';
	}


	/**
	 * @return string
	 */
	public function getPreviewImageHttp() {
		return $this->getHttpPath() . '/' . $this->getTitle() . '_preview.png';
	}


	/**
	 * @return string
	 */
	public function getPosterHttp() {
		return $this->getHttpPath() . '/' . $this->getTitle() . '_poster.png';
	}


	/**
	 * @return string
	 */
	public function getImagePath() {
		return $this->getPath() . '/' . rtrim($this->getTitle(), '.' . $this->getSuffix()) . '_poster';
	}


	/**
	 * @return bool
	 */
	public function getStatusConvert() {
		/**
		 * @var $mediaConverter mcMedia
		 */
		$mediaConverter = mcMedia::where(array( 'trigger_obj_id' => $this->getId() ))->first();
		if ($mediaConverter) {
			return $mediaConverter->getStatusConvert();
		} else {
			return false;
		}
	}
} 