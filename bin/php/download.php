<?php
/**
 * @package VK Saver
 * @author  Serhey Dolgushev <dolgushev.serhey@gmail.com>
 * @date    27 Dec 2012
 **/

if( php_sapi_name() != 'cli' ) {
	exit( 'This script could be run only in CLI mode' . "\n" );
}

require_once( 'helpers/download.php' );
require_once( 'lib/vk/vkapi.php' );

$download = new DownloadHelper( false );
$lists    = $download->getAllDownloadLists();
foreach( $lists as $sessionID ) {
	$download->setSessionID( $sessionID );
	$list = $download->getDownloadList();

	if( (int) $list['status'] !== DownloadHelper::STATUS_CREATED ) {
		continue;
	}

	$list['status'] = DownloadHelper::STATUS_DOWNLOADING;
	$download->storeDownoadListInfo( $list );

	foreach( $list['audios'] as $key => $audio ) {
		if( (bool) $audio['is_downloaded'] ) {
			continue;
		}

		if(
			isset( $audio['errors_count'] )
			&& (int) $audio['errors_count'] >= 3
		) {
			$list['audios'][ $key ]['skipped'] = 1;
			$download->storeDownoadListInfo( $list );
			continue;
		}

		$result = $download->downloadAudio( $audio );
		if( $result === false ) {
			if( isset( $audio['errors_count'] ) === false ) {
				$audio['errors_count'] = 1;
			} else {
				$audio['errors_count']++;
			}
		} else {
			$audio['is_downloaded'] = 1;
		}
		$list['audios'][ $key ] = $audio;

		// Audio was not downloaded, it will be downloaded in the next run
		if( $result === false ) {
			$list['status'] = DownloadHelper::STATUS_CREATED;
		}
		$download->storeDownoadListInfo( $list );
		if( $result === false ) {
			exit();
		}
	}

	$download->createZIP();
	$list['status'] = DownloadHelper::STATUS_DOWNLOADED;
	$download->storeDownoadListInfo( $list );
	exit();
}
