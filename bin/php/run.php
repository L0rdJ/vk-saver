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

$i = 0;
$download = new DownloadHelper( false );
$lists    = $download->getAllDownloadLists();
foreach( $lists as $sessionID ) {
	$download->setSessionID( $sessionID );
	$list = $download->getDownloadList();

	if( (int) $list['status'] === DownloadHelper::STATUS_CREATED ) {
		$i++;
		exec( '$(which php) bin/php/download_list.php ' . $sessionID . ' &> /dev/null &' );
	} elseif(
		(int) $list['status'] === DownloadHelper::STATUS_DOWNLOADED
		&& isset( $list['sync_status'] )
		&& (int) $list['sync_status'] === DownloadHelper::SYNC_STATUS_TOKEN_STORED
	) {
		exec( '$(which php) bin/php/sync_list.php ' . $sessionID . ' &> /dev/null &' );
	}
}
echo( $i . ' processes has been started' . "\n" );

