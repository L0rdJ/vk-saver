<?php
/**
 * @package VK Saver
 * @class   ControllerSaver
 * @author  Serhey Dolgushev <dolgushev.serhey@gmail.com>
 * @date    20 Dec 2012
 **/

class ControllerSaver extends Controller
{
	private static $maxRecords = 5;
	private static $maxStorage = 1024;

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
			exec( 'du -s --block-size=1M download | nawk \'{print $1}\'', $output );
			if( (int) $output[0] > self::$maxStorage ) {
				$this->addMessage( 'К сожалению, сервер перегружен. Повторите попытку позже.' );
				$this->redirect( $this->getURL( 'saver' ) );
			}

			if( $download->isActiveDownloadList() ) {
				$this->addMessage( 'У Вас есть незавершенные загрузки. Попробуйте еще раз, после их завершения.' );
				$this->redirect( $this->getURL( 'saver', 'download' ) );
			}

			$audioIDs = (array) $this->post['selected_audios'];
			if( count( $audioIDs ) > self::$maxRecords ) {
				$this->addMessage( 'Вы можете скачать только ' . self::$maxRecords . ' песен за один раз' );
				$audioIDs = array_slice( $audioIDs, 0, self::$maxRecords );
			}

			$audios = array();
			$offset = 0;
			$limit  = 10;
			do {
				$audioPartIDs = array_slice( $audioIDs, $offset, $limit );
				$offset       += $limit;
				if( count( $audioPartIDs ) > 0 ) {
					$audios = array_merge( $audios, self::getAudios( $audioPartIDs ) );
				}
			} while( count( $audioPartIDs ) > 0 );

			if( count( $audios ) === 0 ) {
				throw new Exception( 'Нет выбранных аудиозписей' );
			}
			$download->createDownloadList( $audios );
			$this->addMessage( 'Список загрузки создан. Отмеченные аудиозаписи начнут загружаться в течении нескольких минут.', 'success' );
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

	public function doSearch() {
		$q      = isset( $this->get['q'] ) ? $this->get['q'] : null;
		$sort   = isset( $this->get['sort'] ) ? $this->get['sort'] : 2;
		$audios = array();

		if(
			strlen( $q ) === 0
			&& isset( $this->get['q'] )
		) {
			$this->addMessage( 'Не введена искомая фраза' );
		}

		if( strlen( $q ) > 0 ) {
			$audios = self::searchAudios( $q, $sort );
			if( count( $audios ) === 0 ) {
				$this->addMessage( 'Ничего не найдено' );
			}
		}

		self::renderView(
			'search',
			array(
				'path'   => 'Поиск',
				'q'      => $q,
				'sort'   => $sort,
				'audios' => $audios
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

		return self::setAudioDurations( $audios );
	}

	public static function searchAudios( $q, $sort ) {
		$r = VKAPI::getInstance()->request(
			'audio.search',
			array(
				'q'     => $q,
				'sort'  => (int) $sort,
				'count' => 200
			),
			-1
		);
		if( isset( $r['error'] ) ) {
			throw new Exception( $r['error']['error_msg'] );
		}
		$audios = $r['response'];
		unset( $audios[0] );

		return self::setAudioDurations( $audios );
	}

	private static function setAudioDurations( array $audios ) {
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
			'audio.getById',
			array( 'audios' => implode( ',', $ids ) ),
			3600
		);
		if( isset( $r['error'] ) ) {
			throw new Exception( $r['error']['error_msg'] );
		}
		return $r['response'];
	}
}
