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

	const A_WIDTH = 178;
	const A_HEIGHT = 100;
	/**
	 * @var int
	 */
	protected $MCId;
	/**
	 * @var int
	 */
	protected $height = 0;
	/**
	 * @var int
	 */
	protected $width = 0;


	/**
	 * @param int $id
	 */
	public function __construct($id = 0) {
		$this->type = 'vid';
		parent::__construct($id);
		if ($id) {
			$dimensions = vmFFmpeg::getVideoDimension($this->getPath() . '/' . $this->getFileName());
			$this->setHeight($dimensions['height']);
			$this->setWidth($dimensions['width']);
		}
	}


	/**
	 * @param string $tmp_path
	 *
	 * @return bool
	 */
	public function uploadVideo($tmp_path, $image_at_second = -1) {
		move_uploaded_file($tmp_path, $this->getPath() . '/' . $this->getTitle() . '.' . $this->getSuffix());

		$video_duration = vmFFmpeg::getDuration($this->getAbsolutePath());
		if ($image_at_second < 0 || $image_at_second > $video_duration) {
			$image_at_second = $video_duration / 3;
		}
		vmFFmpeg::extractImage($this->getAbsolutePath(), $this->getTitle()
			. '_poster.png', $this->getPath(), $image_at_second);
		ilUtil::resizeImage($this->getPoster(), $this->getPreviewImage(), self::A_WIDTH, self::A_HEIGHT, true);

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
	 * @return int
	 */
	public function getHeight() {
		return $this->height;
	}


	/**
	 * @param int $height
	 */
	public function setHeight($height) {
		$this->height = $height;
	}


	/**
	 * @return int
	 */
	public function getWidth() {
		return $this->width;
	}


	/**
	 * @param int $width
	 */
	public function setWidth($width) {
		$this->width = $width;
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