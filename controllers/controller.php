<?php
/**
 * @package VK Saver
 * @class   Controller
 * @author  Serhey Dolgushev <dolgushev.serhey@gmail.com>
 * @date    20 Dec 2012
 **/

abstract class Controller
{
	protected $get  = array();
	protected $post = array();

	public function __construct() {
		$this->get  = $_GET;
		$this->post = $_POST;
	}

	public static function renderView( $tpl, array $data = null ) {
		if( $data === null ) {
			$data = array();
		}

		$user = ControllerAuth::getUserData();
		if( $user !== false ) {
			$data['user'] = $user;
		}

		$data['progress'] = DownloadHelper::getProgress();

		$messages = self::getMessages();
		self::clearMessages();

		$smarty = new Smarty;
		$smarty->debugging = false;
		$smarty->caching   = false;
		$smarty->assign( 'data', $data );
		$smarty->assign( 'messages', $messages );
		$smarty->setTemplateDir( '../views' );
		$smarty->setCacheDir( '../cache/templates' );
		$smarty->setCompileDir( '../cache/complie' );
		$smarty->display( $tpl . '.tpl' );
	}

	public function addMessage( $message, $type = 'error' ) {
		$messages = self::getMessages();
		$messages[] = array(
			'text' => $message,
			'type' => $type
		);
		$_SESSION['messages'] = $messages;
	}

	public static function getMessages() {
		return isset( $_SESSION['messages'] ) ? (array) $_SESSION['messages'] : array();
	}

	public static function clearMessages() {
		$_SESSION['messages'] = array();
	}

	public function getURL( $module, $action = null ) {
		$url = '/?module=' . $module;
		if( $action !== null ) {
			$url .= '&action=' . $action;
		}
		return $url;
	}

	public function redirect( $url ) {
		header( 'Location: ' . $url );
		exit();
	}
}
