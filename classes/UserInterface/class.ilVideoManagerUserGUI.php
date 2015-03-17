<?php
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerPlugin.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerSubscription.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/UserInterface/class.ilVideoManagerVideoTableGUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/UserInterface/class.ilVideoManagerPlayVideoGUI.php');
require_once('./Services/Form/classes/class.ilTextInputGUI.php');
require_once("./Services/Rating/classes/class.ilRatingGUI.php");

/**
 * Class ilVideoManagerUserGUI
 *
 * @ilCtrl_IsCalledBy ilVideoManagerUserGUI: ilRouterGUI, ilUIPluginRouterGUI
 * @ilCtrl_Calls      ilVideoManagerUserGUI: ilVideoManagerVideoTableGUI, ilVideoManagerPlayVideoGUI, ilRatingGUI
 */
class ilVideoManagerUserGUI {

	/**
	 * @var ilCtrl
	 */
	public $ctrl;
	/**
	 * @var ilTemplate
	 */
	public $tpl;
	/**
	 * @var ilTabsGUI
	 */
	public $tabs_gui;
	/**
	 * @var ilToolbarGUI
	 */
	protected $toolbar;
	/**
	 * @var ilVideoManagerPlugin
	 */
	protected $pl;
	/**
	 * @var ilObjUser
	 */
	protected $usr;


	public function __construct() {
		global $tpl, $ilUser, $ilCtrl, $ilToolbar;

		$this->usr = $ilUser;
		$this->pl = ilVideoManagerPlugin::getInstance();
		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->toolbar = $ilToolbar;
	}


	public function executeCommand() {
		$next_class = $this->ctrl->getNextClass();
		$cmd = $this->ctrl->getCmd('view');

		switch ($next_class) {
			case 'ilratinggui':
				$rating = new ilRatingGUI();
				$rating->setObject($_GET['node_id'], 'vid');
				$rating->saveRating();
				$this->ctrl->setParameter($this, 'node_id', $_GET['node_id']);
				$this->ctrl->redirect($this, 'playVideo');
				break;
			default:
				if ($cmd == 'view') {
					unset($_SESSION['search_value']);
				}
				$this->prepareOutput();

				switch ($cmd) {
					case 'view':
						$this->view();
						break;
					case 'performSearch':
						$this->performSearch();
						break;
					case 'search':
						$this->search();
						break;
					case 'playVideo':
						$this->playVideo();
						break;
					case 'subscribe':
						$this->subscribe();
						break;
					case 'unsubscribe':
						$this->unsubscribe();
						break;
				}
		}
		if (videoman::is50()) {
			/**
			 * @var $tpl ilTemplate
			 */
			global $tpl;
			$tpl->getStandardTemplate();
			$tpl->show();
		}
	}


	protected function view() {
		$this->tpl->addCss('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/templates/css/search_table.css');

		$options = array(
			'cmd' => 'view',
			'sort_create_date' => 'ASC',
			'limit' => 8,
		);

		$starter_gui = new ilVideoManagerVideoTableGUI($this, $options);

		$this->tpl->setContent($starter_gui->getHTML());
	}


	protected function playVideo() {
		$video_gui = new ilVideoManagerPlayVideoGUI($this);
		$video_gui->init();
	}


	public function prepareOutput() {
		$this->tpl->addCss('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/templates/css/video_player.css');

		$textinput = new ilTextInputGUI('search_input', 'search_value');
		if (! $_SESSION['search_method'] == 'category') {
			$textinput->setValue($_SESSION['search_value']);
		}

		$this->toolbar->addInputItem($textinput);
		$this->toolbar->addFormButton($this->pl->txt('common_search'), 'search');
		$this->toolbar->setFormAction($this->ctrl->getLinkTarget($this, 'search'));
	}


	public function search() {
		if (array_key_exists('search_value', $_POST)) {
			$_SESSION['search_value'] = $_POST['search_value'];
			$_SESSION['search_method'] = 'all';
		} elseif ($_GET['search_value'] && $_GET['search_method']) {
			$_SESSION['search_value'] = $_GET['search_value'];
			$_SESSION['search_method'] = $_GET['search_method'];
		}

		$this->ctrl->redirect($this, 'performSearch');
	}


	public function performSearch() {
		$this->tpl->addCss('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/templates/css/search_table.css');
		$this->tpl->addBlockFile('ADM_CONTENT', 'search_gui', 'tpl.search_gui.html', 'Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager');
		$this->tpl->setCurrentBlock('search_gui');

		if (array_key_exists('search_value', $_SESSION)) {
			$search = array(
				'value' => $_SESSION['search_value'],
				'method' => $_SESSION['search_method']
			);
		} else {
			ilUtil::sendFailure('Error: no search value given');

			return false;
		}

		$options = array(
			'cmd' => 'performSearch',
			'search' => $search,
			'sort_create_date' => 'ASC',
		);

		unset($_SESSION['table']);
		$search_results = new ilVideoManagerVideoTableGUI($this, $options);
		$this->tpl->setVariable('TABLE', $search_results->getHTML());

		if ($_SESSION['search_method'] == 'category') {
			$this->tpl->setVariable('CHANNEL',
				$this->pl->txt('common_category') . ": '" . ilVideoManagerFolder::find($_SESSION['search_value'])->getTitle() . "'");

			if (ilVideoManagerSubscription::isSubscribed($this->usr->getId(), $_SESSION['search_value'])) {
				$this->ctrl->saveParameter($this, 'video_tbl_table_nav');
				$this->tpl->setVariable('SUBSCRIBE_LINK', $this->ctrl->getLinkTarget($this, 'unsubscribe'));
				$this->tpl->setVariable('SUBSCRIBE', $this->pl->txt('tbl_unsubscribe'));
			} else {
				$this->ctrl->saveParameter($this, 'video_tbl_table_nav');
				$this->tpl->setVariable('SUBSCRIBE_LINK', $this->ctrl->getLinkTarget($this, 'subscribe'));
				$this->tpl->setVariable('SUBSCRIBE', $this->pl->txt('tbl_subscribe'));
			}
		}
	}


	protected function subscribe() {
		$subscription = new ilVideoManagerSubscription();
		$subscription->setUsrId($this->usr->getId());
		$subscription->setCatId($_SESSION['search_value']);
		$subscription->create();

		ilUtil::sendSuccess($this->pl->txt('msg_subscribed_successfully'), true);
		$this->ctrl->saveParameter($this, 'video_tbl_table_nav');
		$this->ctrl->redirect($this, 'performSearch');
	}


	protected function unsubscribe() {
		$subscription = ilVideoManagerSubscription::where(array( 'usr_id' => $this->usr->getId(), 'cat_id' => $_SESSION['search_value'] ))->first();
		$subscription->delete();

		ilUtil::sendSuccess($this->pl->txt('msg_unsubscribed_successfully'), true);
		$this->ctrl->saveParameter($this, 'video_tbl_table_nav');
		$this->ctrl->redirect($this, 'performSearch');
	}
} 