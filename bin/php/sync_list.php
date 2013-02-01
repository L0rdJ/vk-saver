<?php
/**
 * @package VK Saver
 * @author  Serhey Dolgushev <dolgushev.serhey@gmail.com>
 * @date    03 Jan 2013
 **/

if( php_sapi_name() != 'cli' ) {
	exit( 'This script could be run only in CLI mode' . "\n" );
}

if( isset( $argv[1] ) === false ) {
	exit();
}

set_time_limit( 3600 );
require_once( 'controllers/controller.php' );
require_once( 'controllers/google_drive.php' );
require_once( 'helpers/download.php' );
require_once( 'lib/vk/vkapi.php' );
require_once( 'lib/google_api/Google_Client.php' );
require_once( 'lib/google_api/contrib/Google_DriveService.php' );

$download = new DownloadHelper( $argv[1] );
$list     = $download->getDownloadList();

if(
	(int) $list['sync_status'] !== DownloadHelper::SYNC_STATUS_TOKEN_STORED
	|| isset( $list['sync_token'] ) === false
) {
	exit();
}

$client = ControllerGoogleDrive::getClient();
try{
	$client->setAccessToken( $list['sync_token'] );
	$service = new Google_DriveService( $client );
} catch( Exception $e ) {
	// Not valid token
	$download->completeSync();
	exit();
}

$GDriveFolder   = 'VK Music';
$GDrivefolderID = false;

// Check and create folder
$result = $service->files->listFiles(
	array(
		'q'          => "title = '" . $GDriveFolder . "' and mimeType = 'application/vnd.google-apps.folder'",
		'maxResults' => 1
	)
);
if( count( $result['items'] ) === 0 ) {
	// Create folder
	$folder = new Google_DriveFile();
	$folder->setTitle( $GDriveFolder );
	$folder->setMimeType( 'application/vnd.google-apps.folder' );
	$folder = $service->files->insert(
		$folder,
		array( 'mimeType' => 'application/vnd.google-apps.folder' )
	);
	$GDrivefolderID = $folder['id'];
} else {
	$GDrivefolderID = $result['items'][0]['id'];
}

if( $GDrivefolderID === false ) {
	$download->completeSync();
	exit();
}

$list['sync_status'] = DownloadHelper::SYNC_STATUS_ACTIVE;
$download->storeDownoadListInfo( $list );

$parent = new Google_ParentReference();
$parent->setId( $GDrivefolderID );
foreach( $list['audios'] as $key => $audio ) {
	if(
		isset( $audio['is_sync_complete'] ) === false
		|| (bool) $audio['is_sync_complete']
	) {
		continue;
	}

	$fileName = str_replace( "'", '', $audio['download_name'] );
	$q = "title = '" . $fileName . "' and mimeType = 'audio/mpeg'";
	$r = $service->children->listChildren(
		$GDrivefolderID,
		array(
			'q'          => $q,
			'maxResults' => 1
		)
	);
	if( count( $r['items'] ) === 0 ) {
		$file = new Google_DriveFile();
		$file->setTitle( $fileName );
		$file->setMimeType( 'audio/mpeg' );
		$file->setParents( array( $parent ) );

		$path = $download->getAudioDownloadDirectory() . '/' . $audio['download_name'];
		$data = file_get_contents( $path );

		try{
			$service->files->insert(
				$file,
				array(
					'data'     => $data,
					'mimeType' => 'audio/mpeg'
				)
			);
		} catch( Exception $e ) {}
	}
	$list['audios'][ $key ]['is_sync_complete'] = 1;
	$download->storeDownoadListInfo( $list );
}

$list['sync_status'] = DownloadHelper::SYNC_STATUS_COMPLETE;
$download->storeDownoadListInfo( $list );
