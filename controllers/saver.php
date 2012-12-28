<?php
/**
 * @package VK Saver
 * @class   ControllerSaver
 * @author  Serhey Dolgushev <dolgushev.serhey@gmail.com>
 * @date    20 Dec 2012
 **/

class ControllerSaver extends Controller
{
	public function __construct() {
		parent::__construct();

		$data = ControllerAuth::getUserData();
		if( $data === false ) {
			self::renderView( 'login' );
			exit();
		}
	}

	public function doIndex() {
		self::renderView(
			'my_audios',
			array(
				'path'   => 'My audios',
				'audios' => self::getAllAudios()
			)
		);
	}

	public function doDownload() {
		$download = new DownloadHelper();
		if( isset( $this->post['selected_audios'] ) ) {
			if( $download->isActiveDownloadList() ) {
				$this->addMessage( 'There is active download list for current session' );
				$this->redirect( $this->getURL( 'saver', 'download' ) );
			}

			$audioIDs = (array) $this->post['selected_audios'];
			if( count( $audioIDs ) > 5 ) {
				$this->addMessage( 'You can download only 5 songs by one download' );
				$audioIDs = array_slice( $audioIDs, 0, 5 );
			}

			$audios = self::getAudios( $audioIDs );
			if( count( $audios ) === 0 ) {
				throw new Exception( 'No selected audios' );
			}
			$download->createDownloadList( $audios );
			$this->addMessage( 'Download list has been created. Download will start soonly.', 'success' );
		}

		$list = $download->getDownloadList();
		if( $list === false ) {
			$this->addMessage( 'There is no active download list for current session' );
			$this->redirect( $this->getURL( 'saver' ) );
		}

		if( $list['status'] !== DownloadHelper::STATUS_DOWNLOADED ) {
 			header( 'Refresh: 5' );
		} else {
			$this->addMessage( 'Downloading has been finished', 'success' );
		}

		self::renderView(
			'download',
			array(
				'path'       => 'Download list',
				'list'       => $list,
				'session_id' => session_id()
			)
		);
	}

	public static function getAllAudios() {
		$api = VKAPI::getInstance();
		$r   = $api->request( 'audio.get', array(), -1 );
		if( isset( $r['error'] ) ) {
			throw new Exception( $r['error']['error_msg'] );
		}
		$audios = $r['response'];

		foreach( $audios as $key => $audio ) {
			$h = (int) ( $audio['duration'] / 3600 );
			$m = sprintf( '%02d', (int) ( ( $audio['duration'] / 60 ) % 60 ) );
			$s = sprintf( '%02d', $audio['duration'] % 60 );
			$duration = $m . ':' . $s;
			if( $h > 0 ) {
				$duration = $h . ':' . $duration;
			}
			$audios[ $key ]['duration_f'] = $duration;
		}

		return $audios;
	}

	public function getAudios( array $ids ) {
		$r = VKAPI::getInstance()->request(
			'audio.get',
			array( 'aids' => implode( ',', $ids ) ),
			3600
		);
		if( isset( $r['error'] ) ) {
			throw new Exception( $r['error']['error_msg'] );
		}
		return $r['response'];
	}
}
