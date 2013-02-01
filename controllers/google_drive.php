<?php
/**
 * @package VK Saver
 * @class   ControllerGoogleDrive
 * @author  Serhey Dolgushev <dolgushev.serhey@gmail.com>
 * @date    03 Jan 2013
 **/

class ControllerGoogleDrive extends Controller
{
	const CLIENT_ID     = '888150503182.apps.googleusercontent.com';
	const CLIENT_SECRET = 'tvgXCrs1TBphH0o-8IQ5mB43';

	private static $client = null;

	public static function getClient() {
		if( self::$client === null ) {
			$url = 'http://' . @$_SERVER['SERVER_NAME'] . '/?module=googleDrive&action=getToken';
			self::$client = new Google_Client();
			self::$client->setClientId( self::CLIENT_ID );
			self::$client->setClientSecret( self::CLIENT_SECRET );
			self::$client->setRedirectUri( $url );
			self::$client->setScopes( array( 'https://www.googleapis.com/auth/drive' ) );
		}

		return self::$client;
	}

	public function doSync() {
		$download = new DownloadHelper();
		if( isset( $this->post['selected_audios'] ) ) {
			if( $download->isActiveDownloadList() ) {
				$this->addMessage( 'У Вас есть незавершенные загрузки. Синхронизация с Google Drive возможна только после завершения всех загрузок.' );
				$this->redirect( $this->getURL( 'saver', 'download' ) );
			}

			if( $download->isActiveSyncList() ) {
				$this->addMessage( 'У Вас есть незавершенные cинхронизации Google Drive.' );
				$this->redirect( $this->getURL( 'googleDrive', 'sync' ) );
			}

			$audioIDs = (array) $this->post['selected_audios'];
			if( count( $audioIDs ) === 0 ) {
				throw new Exception( 'Нет выбранных аудиозписей' );
				$this->redirect( $this->getURL( 'saver', 'download' ) );
			}
			$download->startSync( $audioIDs );
			$this->addMessage( 'Синхронизация с Google Drive начнеться в течении нескольких минут', 'success' );

			$client = self::getClient();
			$this->redirect( $client->createAuthUrl() );
		}

		$list = $download->getDownloadList();
		if(
			$list === false
			|| isset( $list['sync_status'] ) === false
		) {
			$this->addMessage( 'У Вас нет активных cинхронизаций с Google Drive. Для начала синхронизации загрузите аудиозаписи.' );
			$this->redirect( $this->getURL( 'saver' ) );
		}

		$GDriveFolder = false;
		if( $list['sync_status'] !== DownloadHelper::SYNC_STATUS_COMPLETE ) {
 			header( 'Refresh: 5' );
		} else {
			$this->addMessage( 'Синхронизация успешно окончена', 'success' );
			$client = self::getClient();
			try{
				$client->setAccessToken( $list['sync_token'] );
				$service = new Google_DriveService( $client );
				$q = "title = 'VK Music' and mimeType = 'application/vnd.google-apps.folder'";
				$r = $service->files->listFiles(
					array(
						'q'          => $q,
						'maxResults' => 1
					)
				);
				if( count( $r['items'] ) > 0 ) {
					$GDriveFolder = $r['items'][0];
				}
			} catch( Exception $e ) {}
		}

		self::renderView(
			'sync',
			array(
				'path'   => 'Синхронизации',
				'list'   => $list,
				'folder' => $GDriveFolder
			)
		);
	}

	public function doGetToken() {
		$client = self::getClient();
		if( isset( $this->get['code'] ) ) {
			$accessToken = $client->authenticate( $this->get['code'] );

			$download = new DownloadHelper();
			$list     = $download->getDownloadList();
			$list['sync_token']  = $accessToken;
			$list['sync_status'] = DownloadHelper::SYNC_STATUS_TOKEN_STORED;
			$download->storeDownoadListInfo( $list );

			$this->redirect( $this->getURL( 'googleDrive', 'sync' ) );
		} else {
			throw new Exception( 'Could not extract access Auth code' );
		}
	}
}
