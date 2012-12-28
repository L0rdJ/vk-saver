<?php
/**
 * @package VK Saver
 * @class   ControllerIndex
 * @author  Serhey Dolgushev <dolgushev.serhey@gmail.com>
 * @date    20 Dec 2012
 **/

class ControllerIndex extends Controller
{
	public function doIndex() {
		if( isset( $_SESSION['user_id'] ) ) {
			$this->redirect( $this->getURL( 'saver', 'index' ) );
		} else {
			self::renderView( 'login' );
		}
	}
}
