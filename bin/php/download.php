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

	if( (int) $list['status'] !== DownloadHelper::STATUS_CREATED ) {
		continue;
	}

	$i++;
	exec( '$(which php) bin/php/process_list.php ' . $sessionID . ' &> /dev/null &' );
}
echo( $i . ' processes has been started' . "\n" );

