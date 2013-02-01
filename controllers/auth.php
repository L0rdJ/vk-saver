<?php
/**
 * @package VK Saver
 * @class   ControllerAuth
 * @author  Serhey Dolgushev <dolgushev.serhey@gmail.com>
 * @date    20 Dec 2012
 **/

class ControllerAuth extends Controller
{
	public static function getUserData() {
		if( isset( $_SESSION['user_data'] ) === false ) {
			$data = VKAPI::getInstance()->request( 'users.get' );
			if( isset( $data['response'] ) ) {
				$_SESSION['user_data'] = json_encode( $data['response'][0] );
			} else {
				return false;
			}
		}
		return json_decode( $_SESSION['user_data'], true );
	}

	public function doRequestToken() {
		$api = VKAPI::getInstance();
		$url = $api->getAuthURL(
			array(
				'redirect_uri' => 'http://' . $_SERVER['SERVER_NAME'] . '/?module=auth&action=blank',
				'scope'        => 'audio,offline'
			)
		);
		$this->redirect( $url );
	}

	public function doBlank() {
		self::renderView( 'auth/blank' );
	}

	public function doGetToken() {
		if(
			isset( $this->get['access_token'] )
			&& isset( $this->get['user_id'] )
		) {
			$_SESSION['access_token'] = $this->get['access_token'];
			$_SESSION['user_id']      = $this->get['user_id'];

			$this->redirect( '/' );
		} else {
			throw new Exception( 'Could not extract access token' );
		}
	}

	public function doLogout() {
		session_destroy();
		$this->redirect( '/' );
	}
}
