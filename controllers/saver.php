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
				'path'   => 'Мои Аудиозаписи',
				'audios' => self::getAllAudios()
			)
		);
	}

	public function doDownload() {
		$download = new DownloadHelper();
		if( isset( $this->post['selected_audios'] ) ) {
			if( $download->isActiveDownloadList() ) {
				$this->addMessage( 'У Вас есть незавершенные загрузки. Попробуйте еще раз, после их завершения.' );
				$this->redirect( $this->getURL( 'saver', 'download' ) );
			}

			$audioIDs = (array) $this->post['selected_audios'];
			if( count( $audioIDs ) > 20 ) {
				$this->addMessage( 'Вы можете скачать только 20 песен за один раз' );
				$audioIDs = array_slice( $audioIDs, 0, 20 );
			}

			$audios = self::getAudios( $audioIDs );
			if( count( $audios ) === 0 ) {
				throw new Exception( 'No selected audios' );
			}
			$download->createDownloadList( $audios );
			$this->addMessage( 'Список загрузки создан. Отмеченные аудиозаписи начнут загружаться в течении нескольих минут.', 'success' );
		}

		$list = $download->getDownloadList();
		if( $list === false ) {
			$this->addMessage( 'У Вас нет незавершенных загрузок' );
			$this->redirect( $this->getURL( 'saver' ) );
		}

		if( $list['status'] !== DownloadHelper::STATUS_DOWNLOADED ) {
 			header( 'Refresh: 5' );
		} else {
			$this->addMessage( 'Загрузка успешно окончена', 'success' );
		}

		self::renderView(
			'download',
			array(
				'path'       => 'Загрузки',
				'list'       => $list,
				'session_id' => session_id()
			)
		);
	}

	public function doDownloadAll() {
		$download = new DownloadHelper();
		$list     = $download->getDownloadList();
		if( (int) $list['status'] !== DownloadHelper::STATUS_DOWNLOADED ) {
			$this->addMessage( 'Текущая загрузка еще не завершена' );
			$this->redirect( $this->getURL( 'saver', 'download' ) );
		}

		set_time_limit( 3600 );
		$download->createZIP();
		$download->downloadZIP();
		exit();
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
